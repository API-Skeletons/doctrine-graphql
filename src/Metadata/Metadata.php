<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Exception\UnmappedEntityMetadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;

class Metadata
{
    protected Driver $driver;
    protected ?array $metadataConfig;

    public function __construct(Driver $driver, ?array $metadataConfig)
    {
        $this->driver = $driver;
        $this->metadataConfig = $metadataConfig;
    }

    public function getEntity($entityClass): Entity
    {
        if (! isset($this->metadataConfig[$entityClass])) {
            throw new UnmappedEntityMetadata(
                'Entity ' . $entityClass . ' is not mapped in metadata');
        }

        return new Entity($this->driver, $this->metadataConfig[$entityClass]);
    }
}
