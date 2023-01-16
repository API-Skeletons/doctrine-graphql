<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Event;

use Doctrine\ORM\QueryBuilder;
use League\Event\HasEventName;

class FilterQueryBuilder implements
    HasEventName
{
    /** @param string[] $entityAliasMap */
    public function __construct(protected QueryBuilder $queryBuilder, protected array $entityAliasMap, protected string $eventName)
    {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /** @return string[] */
    public function getEntityAliasMap(): array
    {
        return $this->entityAliasMap;
    }
}
