<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Association
{
    /** @param string[] $excludeCriteria */
    public function __construct(
        protected string $group = 'default',
        protected string|null $strategy = null,
        protected string|null $description = null,
        protected array $excludeCriteria = [],
    ) {
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getStrategy(): string|null
    {
        return $this->strategy;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    /** @return string[] */
    public function getExcludeCriteria(): array
    {
        return $this->excludeCriteria;
    }
}
