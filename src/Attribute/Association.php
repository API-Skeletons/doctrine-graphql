<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY & Attribute::IS_REPEATABLE)]
class Association
{
    protected string $group;

    protected string $strategy;

    public function __construct(string $group = 'default', string $strategy = null)
    {
        $this->group = $group;
        $this->strategy = $strategy;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
