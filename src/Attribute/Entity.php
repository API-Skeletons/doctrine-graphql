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
    private string|null $description = null;

    /**
     * @var mixed[] An array of filters as
     * [
     *   'condition' => FilterComposite::CONDITION_AND,
     *   'filter' => 'Filter\ClassName',
     * ]
     */
    private array $filters = [];

    /**
     * @param mixed[]  $filters
     * @param string[] $excludeCriteria
     */
    public function __construct(
        string $group = 'default',
        bool $byValue = true,
        string|null $description = null,
        private string|null $typeName = null,
        array $filters = [],
        private string|null $namingStrategy = null,
        private array $excludeCriteria = [],
    ) {
        $this->group       = $group;
        $this->byValue     = $byValue;
        $this->description = $description;
        $this->filters     = $filters;
    }

    public function getGroup(): string|null
    {
        return $this->group;
    }

    public function getByValue(): bool
    {
        return $this->byValue;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getTypeName(): string|null
    {
        return $this->typeName;
    }

    /** @return mixed[] */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getNamingStrategy(): string|null
    {
        return $this->namingStrategy;
    }

    /** @return string[] */
    public function getExcludeCriteria(): array
    {
        return $this->excludeCriteria;
    }
}
