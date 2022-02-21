<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS & Attribute::IS_REPEATABLE)]
final class Entity
{
    /**
     * @var string The GraphQL group
     */
    private string $group;

    /**
     * @var bool Extract by value: true, or by reference: false
     */
    private bool $byValue;

    /**
     * @var string An alternate hydrator classname to use to extract the entity
     */
    private ?string $hydrator;

    /**
     * @var string[] An array of field and collection names to allow,
     *               thereby bypassing individual attribute configuration.
     *               A value of '*' is accepted when used in combination with
     *               the blockFields.  If left empty then Attribute
     *               configuration determines each field's inclusion in the
     *               schema.
     */
    private array $allowFields = [];

    /**
     * @var string[] An array of fields to exclude from schema whether they
     *               have matching group configuration or not.
     */
    private array $blockFields = [];

    /**
     * @var string|null Documentation for the entity within GraphQL
     */
    private ?string $docs = null;

    public function __construct(
        string $group = 'default',
        bool $byValue = true,
        ?string $hydrator = null,
        array $allowFields = [],
        array $blockFields = [],
        ?string $docs = null,
    ) {
        $this->group = $group;
        $this->byValue = $byValue;
        $this->hydrator = $hydrator;
        $this->allowFields = $allowFields;
        $this->blockFields = $blockFields;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function __invoke()
    {
        return [
            'group' => $this->group,
            'byValue' => $this->byValue,
        ];
    }
}
