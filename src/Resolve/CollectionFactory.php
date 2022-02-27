<?php

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use GraphQL\Type\Definition\ResolveInfo;

class CollectionFactory
{
    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function get(Entity $entity): \Closure
    {
        return function ($source, $args, $context, ResolveInfo $resolveInfo) use ($entity) {
            $fieldResolver = $this->driver->getFieldResolver();
            $collection = $fieldResolver($source, $args, $context, $resolveInfo);
            $criteria = Criteria::create();
            $orderBy = [];

            $filter = $args['filter'] ?? [];
            $limit = $this->driver->getConfig()->getLimit();

            foreach ($filter as $field => $value) {
                if ($field === '_skip') {
                    $skip = $value;

                    continue;
                }

                if ($field === '_limit') {
                    if ($value <= $limit) {
                        $limit = $value;

                        continue;
                    }
                }

                // Handle other fields as $field_$type: $value
                // Get right-most _text
                $filter = substr($field, strrpos($field, '_') + 1);

                if (strrpos($field, '_') === false) {
                    // Special case for eq `field: value`
                    $criteria->andWhere($criteria->expr()->eq($field, $value));
                } else {
                    $field = substr($field, 0, strrpos($field, '_'));

                    switch ($filter) {
                        case 'eq':
                        case 'neq':
                        case 'lt':
                        case 'lte':
                        case 'gt':
                        case 'gte':
                        case 'isnull':
                        case 'contains':
                        case 'startswith':
                        case 'endswith':
                            $criteria->andWhere($criteria->expr()->$filter($field, $value));
                            break;
                        case 'in':
                        case 'notin':
                            $criteria->andWhere($criteria->expr()->$filter($field, explode(',', $value)));
                            break;
                        case 'between':
                            $values = explode(',', $value);
                            assert(count($values) >= 2, 'Two values are required for between filter');
                            $criteria->andWhere($criteria->expr()->gte($field, $values[0]));
                            $criteria->andWhere($criteria->expr()->lte($field, $values[1]));
                            break;
                        case 'sort':
                            $orderBy[$field] = $value;
                            break;
                        default:
                            break;
                    }
                }
            }
            $criteria->orderBy($orderBy);

            // Convert result to extracted array
            $results = $collection->matching($criteria);
            $resultCollection = new ArrayCollection();
            $hydrator = $entity->getHydrator();

            foreach ($results as $result) {
                if (is_array($result)) {
                    $resultCollection->add($result);
                } else {
                    $resultCollection->add($hydrator->extract($result));
                }
            }

            return $resultCollection->toArray();
        };
    }
}
