<?php

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\QueryBuilder\Filter\Applicator;
use Doctrine\Common\Collections\ArrayCollection;
use GraphQL\Type\Definition\ResolveInfo;

class EntityFactory
{
    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __invoke(Entity $entity): \Closure
    {
        return function($obj, $args, $context, ResolveInfo $info) use ($entity) {
            $entityClass = $entity->getEntityClass();
            // Resolve top level filters
            $filterTypes = $args['filter'] ?? [];
            $filterArray = [];
            $skip = 0;
            $limit = $this->driver->getConfig()->getLimit();

            foreach ($filterTypes as $field => $value) {
                // Parse command filters first
                if ($field == '_skip') {
                    $skip = $value;
                    continue;
                }

                if ($field == '_limit') {
                    if ($value <= $limit) {
                        $limit = $value;
                    }
                    continue;
                }

                // Handle other fields as $field_$type: $value
                // Get right-most _text
                $filter = substr($field, strrpos($field, '_') + 1);

                // Special case for eq `field: value`
                if (strrpos($field, '_') === false) {
                    // Handle field:value
                    $filterArray[$field] = $value;
                } else {
                    $field = substr($field, 0, strrpos($field, '_'));

                    switch ($filter) {
                        case 'contains':
                            $filterArray[$field . '|like'] = '%' . $value . '%';
                            break;
                        case 'startswith':
                            $filterArray[$field . '|like'] = $value . '%';
                            break;
                        case 'endswith':
                            $filterArray[$field . '|like'] = '%' . $value;
                            break;
                        case 'isnull':
                            if ($value === true) {
                                $filterArray[$field . '|isnull'] = true;
                            } else {
                                $filterArray[$field . '|isnotnull'] = true;
                            }
                            break;
                        default:
                            $filterArray[$field . '|' . $filter] = $value;
                            break;
                    }
                }
            }

            $queryBuilderFilter = new Applicator($this->driver->getEntityManager(), $entityClass);
            $queryBuilderFilter->setEntityAlias('entity');
            $queryBuilder = $queryBuilderFilter($filterArray);

            if ($this->driver->getConfig()->getUsePartials()) {
                // Select only the fields being queried
                $fieldArray = $info->getFieldSelection();

                // Add primary key of this entity; required for partials
                $classMetadata = $this->driver->getEntityManager()->getClassMetadata($entityClass);
                $fieldArray[$classMetadata->getSingleIdentifierFieldName()] = 1;

                // Verify all fields exist and only query for scalar values, not associations
                foreach ($fieldArray as $fieldName => $value) {
                    if ($classMetadata->hasAssociation($fieldName)) {
                        unset($fieldArray[$fieldName]);
                    }
                }
                $fieldList = implode(',', array_keys($fieldArray));

                // Build query builder from Query Provider
                $queryBuilder
                    ->select('partial entity.{' . $fieldList . '}')
                    ->from($entityClass, 'entity');
            } else {
                // Build query builder from Query Provider
                $queryBuilder
                    ->select('entity')
                    ->from($entityClass, 'entity');
            }

            if ($skip) {
                $queryBuilder->setFirstResult($skip);
            }

            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            // Convert result to extracted array
            $results = $queryBuilder->getQuery()->getResult();
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
