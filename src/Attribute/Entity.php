<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Entity
{
    /** @var string The GraphQL group */
    private string $group;

    /** @var bool Extract by value: true, or by reference: false */
    private bool $byValue;

    /** @var string The hydrator classname to used extract the entity */
    private string $hydrator;

    /** @var string|null Documentation for the entity within GraphQL */
    private ?string $docs = null;

    /**
     * @var mixed[] An array of filters as
     * [
     *   'condition' => FilterComposite::CONDITION_AND,
     *   'filter' => 'Filter\ClassName',
     * ]
     */
    private array $filters = [];

    private ?string $namingStrategy;

    /**
     * @param mixed[] $filters
     */
    public function __construct(
        string $group = 'default',
        bool $byValue = true,
        string $hydrator = 'default',
        ?string $docs = null,
        ?string $typeName = null,
        array $filters = [],
        ?string $namingStrategy = null,
    ) {
        $this->group          = $group;
        $this->byValue        = $byValue;
        $this->hydrator       = $hydrator;
        $this->docs           = $docs;
        $this->typeName       = $typeName;
        $this->filters        = $filters;
        $this->namingStrategy = $namingStrategy;
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

    public function getDocs(): ?string
    {
        return $this->docs;
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }

    /**
     * @return mixed[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getNamingStrategy(): ?string
    {
        return $this->namingStrategy;
    }
}
