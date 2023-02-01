<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Event;

use Doctrine\Common\Collections\Criteria;
use GraphQL\Type\Definition\ResolveInfo;
use League\Event\HasEventName;

class FilterCriteria implements
    HasEventName
{
    public function __construct(
        protected Criteria $criteria,
        protected string $eventName,
        protected mixed $objectValue,
        protected array $args,
        protected mixed $context,
        protected ResolveInfo $info,
    ) {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
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
