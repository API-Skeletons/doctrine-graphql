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

    /** @var string|null Documentation for the entity within GraphQL */
    private ?string $description = null;

    private ?string $typeName;

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
     * @var string[]
     */
    private array $excludeCriteria;

    /**
     * @param mixed[] $filters
     * @param string[] $excludeCriteria
     */
    public function __construct(
        string $group = 'default',
        bool $byValue = true,
        ?string $description = null,
        ?string $typeName = null,
        array $filters = [],
        ?string $namingStrategy = null,
        array $excludeCriteria = [],
    ) {
        $this->group          = $group;
        $this->byValue        = $byValue;
        $this->description    = $description;
        $this->typeName       = $typeName;
        $this->filters        = $filters;
        $this->namingStrategy = $namingStrategy;
        $this->excludeCriteria = $excludeCriteria;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getByValue(): bool
    {
        return $this->byValue;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function getExcludeCriteria(): array
    {
        return $this->excludeCriteria;
    }
}
