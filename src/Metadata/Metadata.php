<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Exception\UnmappedEntityMetadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use GraphQL\Error\Error;

class Metadata extends AbstractContainer
{
    protected Driver $driver;
    /** @var mixed[]|null */
    protected ?array $metadataConfig;

    public function __construct(Driver $driver, ?array $metadataConfig)
    {
        $this->driver             = $driver;
        $this->metadataConfig     = $metadataConfig;
    }

    /**
     * @return Entity
     * @throws \GraphQL\Error\Error
     */
    public function get(string $id)
    {
        if (! isset($this->metadataConfig[$id])) {
            throw new Error(
                'Entity ' . $id . ' is not mapped in the metadata'
            );
        }

        if (! $this->has($id)) {
            $this->set($id, new Entity($this->driver, $this->metadataConfig[$id]));
        }

        return parent::get($id);
    }

    public function getMetadataConfig(): mixed
    {
        return $this->metadataConfig;
    }
}
