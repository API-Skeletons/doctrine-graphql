<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use GraphQL\Error\Error;
use Psr\Container\ContainerInterface;

class Metadata extends AbstractContainer
{
    protected ContainerInterface $container;
    /** @var mixed[]|null */
    protected ?array $metadataConfig;

    public function __construct(ContainerInterface $container, ?array $metadataConfig)
    {
        $this->container      = $container;
        $this->metadataConfig = $metadataConfig;
    }

    /**
     * @throws Error
     */
    public function get(string $id): Entity
    {
        if (! isset($this->metadataConfig[$id])) {
            throw new Error(
                'Entity ' . $id . ' is not mapped in the metadata'
            );
        }

        if (! $this->has($id)) {
            $this->set($id, new Entity($this->container, $this->metadataConfig[$id]));
        }

        return parent::get($id);
    }

    public function getMetadataConfig(): mixed
    {
        return $this->metadataConfig;
    }
}
