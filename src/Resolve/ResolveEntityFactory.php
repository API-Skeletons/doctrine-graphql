<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Criteria\Filters as FiltersDef;
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\QueryBuilder\Filter\Applicator;
use Closure;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GraphQL\Type\Definition\ResolveInfo;
use League\Event\EventDispatcher;

use function base64_decode;
use function base64_encode;
use function implode;

class ResolveEntityFactory
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        protected EventDispatcher $eventDispatcher,
    ) {
    }

    public function get(Entity $entity, string $eventName): Closure
    {
        return function ($objectValue, array $args, $context, ResolveInfo $info) use ($entity, $eventName) {
            $entityClass = $entity->getEntityClass();

            $queryBuilderFilter = (new Applicator($this->entityManager, $entityClass))
                ->setEntityAlias('entity');
            $queryBuilder       = $queryBuilderFilter($this->buildFilterArray($args['filter'] ?? []))
                ->select('entity');

            return $this->buildPagination(
                queryBuilder: $queryBuilder,
                aliasMap: $queryBuilderFilter->getEntityAliasMap(),
                eventName: $eventName,
                objectValue: $objectValue,
                args: $args,
                context: $context,
                info: $info,
            );
        };
    }

    /**
     * @param mixed[] $filterTypes
     *
     * @return mixed[]
     */
    private function buildFilterArray(array $filterTypes): array
    {
        $filterArray = [];

        foreach ($filterTypes as $field => $filters) {
            foreach ($filters as $filter => $value) {
                switch ($filter) {
                    case FiltersDef::CONTAINS:
                        $filterArray[$field . '|like'] = $value;
                        break;
                    case FiltersDef::STARTSWITH:
                        $filterArray[$field . '|startswith'] = $value;
                        break;
                    case FiltersDef::ENDSWITH:
                        $filterArray[$field . '|endswith'] = $value;
                        break;
                    case FiltersDef::ISNULL:
                        $filterArray[$field . '|isnull'] = 'true';
                        break;
                    case FiltersDef::BETWEEN:
                        $filterArray[$field . '|between'] = $value['from'] . ',' . $value['to'];
                        break;
                    case FiltersDef::IN:
                        $filterArray[$field . '|in'] = implode(',', $value);
                        break;
                    case FiltersDef::NOTIN:
                        $filterArray[$field . '|notin'] = implode(',', $value);
                        break;
                    default:
                        $filterArray[$field . '|' . $filter] = (string) $value;
                        break;
                }
            }
        }

        return $filterArray;
    }

    /**
     * @param mixed[] $filterTypes
     * @param mixed[] $aliasMap
     * @param mixed[] $args
     *
     * @return mixed[]
     */
    public function buildPagination(
        QueryBuilder $queryBuilder,
        array $aliasMap,
        string $eventName,
        ...$resolve,
    ): array {
        $first  = 0;
        $after  = 0;
        $last   = 0;
        $before = 0;
        $offset = 0;

        if (isset($resolve['args']['pagination'])) {
            foreach ($resolve['args']['pagination'] as $field => $value) {
                switch ($field) {
                    case 'first':
                        $first = $value;
                        break;
                    case 'after':
                        $after = (int) base64_decode($value, true) + 1;
                        break;
                    case 'last':
                        $last = $value;
                        break;
                    case 'before':
                        $before = (int) base64_decode($value, true);
                        break;
                }
            }
        }

        $limit         = $this->config->getLimit();
        $adjustedLimit = $first ?: $last ?: $limit;
        if ($adjustedLimit < $limit) {
            $limit = $adjustedLimit;
        }

        if ($after) {
            $offset = $after;
        } elseif ($before) {
            $offset = $before - $limit;
        }

        if ($offset < 0) {
            $limit += $offset;
            $offset = 0;
        }

        if ($offset) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        /**
         * Fire the event dispatcher using the passed event name.
         * Include all resolve variables.
         */

        $this->eventDispatcher->dispatch(
            new FilterQueryBuilder(
                $queryBuilder,
                $aliasMap,
                $eventName,
                ...$resolve,
            ),
        );

        $paginator = new Paginator($queryBuilder->getQuery());
        $itemCount = $paginator->count();

        if ($last && ! $before) {
            $offset = $itemCount - $last;
            $queryBuilder->setFirstResult($offset);
            $paginator = new Paginator($queryBuilder->getQuery());
        }

        $edges       = [];
        $index       = 0;
        $lastCursor  = base64_encode((string) 0);
        $firstCursor = null;
        foreach ($paginator->getQuery()->getResult() as $result) {
            $cursor = base64_encode((string) ($index + $offset));

            $edges[] = [
                'node' => $result,
                'cursor' => $cursor,
            ];

            $lastCursor = $cursor;
            if (! $firstCursor) {
                $firstCursor = $cursor;
            }

            $index++;
        }

        $endCursor   = $paginator->count() ? $paginator->count() - 1 : 0;
        $startCursor = base64_encode((string) 0);
        $endCursor   = base64_encode((string) $endCursor);

        return [
            'edges' => $edges,
            'totalCount' => $paginator->count(),
            'pageInfo' => [
                'endCursor' => $endCursor,
                'startCursor' => $startCursor,
                'hasNextPage' => $endCursor !== $lastCursor,
                'hasPreviousPage' => $firstCursor !== null && $startCursor !== $firstCursor,
            ],
        ];
    }
}
