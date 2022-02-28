<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Event;

use Doctrine\ORM\QueryBuilder;
use League\Event\HasEventName;

class FilterQueryBuilder implements
    HasEventName
{
    protected QueryBuilder $queryBuilder;

    /** @var string[] */
    protected array $entityAliasMap;

    /**
     * @param string[] $entityAliasMap
     */
    public function __construct(QueryBuilder $queryBuilder, array $entityAliasMap)
    {
        $this->queryBuilder   = $queryBuilder;
        $this->entityAliasMap = $entityAliasMap;
    }

    public function eventName(): string
    {
        return 'filter.querybuilder';
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return string[]
     */
    public function getEntityAliasMap(): array
    {
        return $this->entityAliasMap;
    }
}
