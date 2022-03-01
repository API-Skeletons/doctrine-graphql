<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Field
{
    protected string $group;

    protected ?string $strategy;

    protected ?string $description;

    public function __construct(
        string $group = 'default',
        ?string $strategy = null,
        ?string $description = null
    ) {
        $this->group       = $group;
        $this->strategy    = $strategy;
        $this->description = $description;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
