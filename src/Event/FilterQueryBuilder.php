<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Event;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use League\Event\HasEventName;

class FilterQueryBuilder implements
    HasEventName
{
    /** @param string[] $entityAliasMap */
    public function __construct(
        protected QueryBuilder $queryBuilder,
        protected array $entityAliasMap,
        protected string $eventName,
        protected $objectValue,
        protected array $args,
        protected $context,
        protected ResolveInfo $info
    ) {
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

    public function getObjectValue(): mixed
    {
        return $this->objectValue;
    }

    /** @return mixed[] */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function getContext(): mixed
    {
        return $this->context;
    }

    public function getInfo(): ResolveInfo
    {
        return $this->info;
    }
}
