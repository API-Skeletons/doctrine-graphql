<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY & Attribute::IS_REPEATABLE)]
final class Collection
{
    private string $group = 'default';
    private array $excludeFilters = [];

    public function __construct(?string $group = null, array $excludeFilters = [])
    {
        $this->group = $group ?? $this->group;
        $this->excludeFilters = $excludeFilters;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function __invoke()
    {
        return [
            'group' => $this->group,
        ];
    }
}
