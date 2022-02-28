<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Exception\UnmappedEntityMetadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;

class Metadata
{
    protected Driver $driver;
    /** @var mixed[]|null */
    protected ?array $metadataConfig;
    /** @var mixed[] */
    protected array $registeredEntities;

    public function __construct(Driver $driver, ?array $metadataConfig)
    {
        $this->driver             = $driver;
        $this->metadataConfig     = $metadataConfig;
        $this->registeredEntities = [];
    }

    public function getEntity(string $entityClass): Entity
    {
        if (! isset($this->metadataConfig[$entityClass])) {
            throw new UnmappedEntityMetadata(
                'Entity ' . $entityClass . ' is not mapped in metadata'
            );
        }

        if (isset($this->registeredEntities[$entityClass])) {
            return $this->registeredEntities[$entityClass];
        }

        $this->registeredEntities[$entityClass] =
            new Entity($this->driver, $this->metadataConfig[$entityClass]);

        return $this->registeredEntities[$entityClass];
    }
}
