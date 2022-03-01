<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use Closure;
use Doctrine\Common\Collections\Criteria;
use GraphQL\Type\Definition\ResolveInfo;

use function assert;
use function count;
use function explode;
use function strrpos;
use function substr;

class ResolveCollectionFactory
{
    protected Config $config;

    protected FieldResolver $fieldResolver;

    public function __construct(Config $config, FieldResolver $fieldResolver)
    {
        $this->config = $config;
        $this->fieldResolver = $fieldResolver;
    }

    public function get(Entity $entity): Closure
    {
        return function ($source, $args, $context, ResolveInfo $resolveInfo) {
            $fieldResolver = $this->fieldResolver;
            $collection    = $fieldResolver($source, $args, $context, $resolveInfo);

            $criteria = Criteria::create();
            $orderBy  = [];

            $filter = $args['filter'] ?? [];
            $limit  = $this->config->getLimit();

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

            // Return entities
            return $collection->matching($criteria);
        };
    }
}
