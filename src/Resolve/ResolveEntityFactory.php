<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\QueryBuilder\Filter\Applicator;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ResolveInfo;
use League\Event\EventDispatcher;

use function array_keys;
use function implode;
use function strrpos;
use function substr;

class ResolveEntityFactory
{
    protected Config $config;

    protected EntityManager $entityManager;

    protected EventDispatcher $eventDispatcher;

    public function __construct(Config $config, EntityManager $entityManager, EventDispatcher $eventDispatcher)
    {
        $this->config          = $config;
        $this->entityManager   = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function get(Entity $entity): Closure
    {
        return function ($obj, $args, $context, ResolveInfo $info) use ($entity) {
            $entityClass = $entity->getEntityClass();
            // Resolve top level filters
            $filterTypes = $args['filter'] ?? [];
            $filterArray = [];
            $skip        = 0;
            $limit       = $this->config->getLimit();

            foreach ($filterTypes as $field => $value) {
                // Parse command filters first
                if ($field === '_skip') {
                    $skip = $value;
                    continue;
                }

                if ($field === '_limit') {
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

            $queryBuilderFilter = (new Applicator($this->entityManager, $entityClass))
                ->setEntityAlias('entity');
            $queryBuilder       = $queryBuilderFilter($filterArray);

            if ($this->config->getUsePartials()) {
                // Select only the fields being queried
                $fieldArray = $info->getFieldSelection();

                // Add primary key of this entity; required for partials
                $classMetadata = $this->entityManager->getClassMetadata($entityClass);

                $fieldArray[$classMetadata->getSingleIdentifierFieldName()] = 1;

                // Verify all fields exist and only query for scalar values, not associations
                foreach ($fieldArray as $fieldName => $value) {
                    if (! $classMetadata->hasAssociation($fieldName)) {
                        continue;
                    }

                    unset($fieldArray[$fieldName]);
                }

                $fieldList = implode(',', array_keys($fieldArray));

                // Build query builder from Query Provider
                $queryBuilder->select('partial entity.{' . $fieldList . '}');
            } else {
                // Build query builder from Query Provider
                $queryBuilder->select('entity');
            }

            if ($skip) {
                $queryBuilder->setFirstResult($skip);
            }

            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            $this->eventDispatcher->dispatch(
                new FilterQueryBuilder($queryBuilder, $queryBuilderFilter->getEntityAliasMap())
            );

            // Return array of entities
            return $queryBuilder->getQuery()->getResult();
        };
    }
}
