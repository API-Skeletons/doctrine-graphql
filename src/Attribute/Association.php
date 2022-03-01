<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Association
{
    protected string $group;

    protected ?string $strategy;

    protected ?string $description;

    /** @var string[] */
    protected array $excludeCriteria;

    /**
     * @param string[] $excludeCriteria
     */
    public function __construct(
        string $group = 'default',
        ?string $strategy = null,
        ?string $description = null,
        array $excludeCriteria = []
    ) {
        $this->group           = $group;
        $this->strategy        = $strategy;
        $this->description     = $description;
        $this->excludeCriteria = $excludeCriteria;
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

    /**
     * @return string[]
     */
    public function getExcludeCriteria(): array
    {
        return $this->excludeCriteria;
    }
}
