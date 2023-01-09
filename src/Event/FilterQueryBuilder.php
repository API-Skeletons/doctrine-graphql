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

    protected string $eventName;

    /**
     * @param string[] $entityAliasMap
     */
    public function __construct(QueryBuilder $queryBuilder, array $entityAliasMap, string $eventName)
    {
        $this->queryBuilder   = $queryBuilder;
        $this->entityAliasMap = $entityAliasMap;
        $this->eventName = $eventName;
    }

    public function eventName(): string
    {
        return $this->eventName;
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
