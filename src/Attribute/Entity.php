<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS & Attribute::IS_REPEATABLE)]
final class Entity
{
    /**
     * @var string The GraphQL group
     */
    private string $group;

    /**
     * @var bool Extract by value: true, or by reference: false
     */
    private bool $byValue;

    /**
     * @var string The hydrator classname to used extract the entity
     */
    private string $hydrator;

    /**
     * @var string|null Documentation for the entity within GraphQL
     */
    private ?string $docs = null;

    public function __construct(
        string $group = 'default',
        bool $byValue = true,
        string $hydrator = 'default',
        ?string $docs = null,
        ?string $typeName = null,
    ) {
        $this->group = $group;
        $this->byValue = $byValue;
        $this->hydrator = $hydrator;
        $this->docs = $docs;
        $this->typeName = $typeName;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getByValue(): bool
    {
        return $this->byValue;
    }

    public function getHydrator(): string
    {
        return $this->hydrator;
    }

    public function getDocs(): string
    {
        return $this->docs;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }
}
