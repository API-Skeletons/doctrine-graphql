<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

#[Attribute]
class Entity
{
    private $group;

    public function __construct(?string $group = null)
    {
        $this->group = $group;
    }

    public function __invoke()
    {
        return [
            'group' => $this->group,
        ];
    }
}
