<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use GraphQL\Error\Error;

class Metadata extends AbstractContainer
{
    public function __construct(
        protected AbstractContainer $container,
        protected array|null $metadataConfig,
    ) {
    }

    public function getContainer(): AbstractContainer
    {
        return $this->container;
    }

    /** @throws Error */
    public function get(string $id): Entity
    {
        if ($this->has($id)) {
            return parent::get($id);
        }

        if (! isset($this->metadataConfig[$id])) {
            throw new Error(
                'Entity ' . $id . ' is not mapped in the metadata',
            );
        }

        return $this->build(Entity::class, $id, $this->metadataConfig[$id]);
    }

    public function getMetadataConfig(): array|null
    {
        return $this->metadataConfig;
    }
}
