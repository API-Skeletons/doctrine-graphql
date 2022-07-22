<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\QueryBuilder\Filter\Applicator;
use Closure;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

            $first       = 0;
            $after       = 0;
            $last        = 0;
            $before      = 0;

            foreach ($filterTypes as $field => $value) {
                // Cursor based pagination
                if ($field === '_first') {
                    $first = $value;
                    continue;
                }
                if ($field === '_after') {
                    $after = (int) base64_decode($value, true) + 1;
                    continue;
                }
                if ($field === '_last') {
                    $last = $value;
                    continue;
                }
                if ($field === '_before') {
                    $before = (int) base64_decode($value, true);
                    continue;
                }

                // Handle other fields as $field_$type: $value
                // Get right-most _text
                $filter = substr($field, strrpos($field, '_') + 1);

                // Special case for eq `field: value`
                if (strrpos($field, '_') === false) {
                    // Handle field:value
                    $filterArray[$field . '|eq'] = $value;
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
                            $filterArray[$field . '|isnull'] = 'true';
                            break;
                        case 'between':
                            $filterArray[$field . '|between'] = $value['from'] . ',' . $value['to'];
                            break;
                        case 'in':
                            $filterArray[$field . '|in'] = implode(',', $value);
                            break;
                        case 'notin':
                            $filterArray[$field . '|notin'] = implode(',', $value);
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

            $offset = 0;
            $limit = $this->config->getLimit();
            $adjustedLimit = $first ?: $last ?: 0;
            if ($adjustedLimit < $limit) {
                $limit = $adjustedLimit;
            }

            if ($after) {
                $offset = $after;
            } else if ($before) {
                $offset = $before - $limit;
            }

            if ($offset < 0) {
                $limit += $offset;
                $offset = 0;
            }

/*
            print_r([
                'offset' => $offset,
                'after' => $after,
                'first' => $first,
                'last' => $last,
                'limit' => $limit,
            ]);
            die();
*/

            if ($offset) {
                $queryBuilder->setFirstResult($offset);
            }

            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            $this->eventDispatcher->dispatch(
                new FilterQueryBuilder($queryBuilder, $queryBuilderFilter->getEntityAliasMap())
            );

            $paginator = new Paginator($queryBuilder->getQuery());

            $edges = [];
            $index = 0;
            foreach ($paginator->getQuery()->getResult() as $result) {
                $cursor = base64_encode((string) ($index + $offset));

                $edges[] = [
                    'node' => $result,
                    'cursor' => $cursor,
                ];

                $lastCursor = $cursor;
                $index ++;
            }

            $endCursor = $paginator->count() ? $paginator->count() - 1: 0;
            $endCursor = base64_encode((string) $endCursor);

            return [
                'edges' => $edges,
                'totalCount' => $paginator->count(),
                'pageInfo' => [
                    'endCursor' => $endCursor,
                    'hasNextPage' => $endCursor !== $lastCursor,
                ],
            ];
        };
    }
}
