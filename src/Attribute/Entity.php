<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

#[Attribute(Attribute::TARGET_CLASS & Attribute::IS_REPEATABLE)]
final class Entity
{
    private string $group = 'default';

    public function __construct(?string $group = null)
    {
        $this->group = $group ?? $this->group;
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
