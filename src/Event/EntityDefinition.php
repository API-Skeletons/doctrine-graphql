<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Event;

use ArrayObject;
use League\Event\HasEventName;

class EntityDefinition implements
    HasEventName
{
    protected ArrayObject $definition;
    protected string $eventName;

    /**
     * @param string[] $entityAliasMap
     */
    public function __construct(ArrayObject $definition, string $eventName)
    {
        $this->definition = $definition;
        $this->eventName  = $eventName;
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function getDefinition(): ArrayObject
    {
        return $this->definition;
    }
}
