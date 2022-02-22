<?php

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Metadata\Factory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\EntityType;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class Driver
{
    /**
     * @var ContainerInterface In order to allow for custom hydrators, a
     *                         container is necessary.
     */
    protected ContainerInterface $container;

    /**
     * @var string
     */
    protected string $entityManagerAlias;

    /**
     * @var Config The config must contain a `group`
     */
    protected Config $config;

    /**
     * @var Metadata
     */
    protected Metadata $metadata;

    /**
     * @param string $entityManagerAlias required
     * @param Config $config required
     * @param Metadata|null $metadata optional so cached metadata can be loaded
     */
    public function __construct(ContainerInterface $container, string $entityManagerAlias, Config $config, ?Metadata $metadata = null)
    {
        $this->container = $container;
        $this->entityManagerAlias = $entityManagerAlias;
        $this->config = $config;

        // Build the metadata from factory
        $metadataFactory = new Factory($container, $this, $metadata);
        $this->metadata = $metadataFactory->getMetadata();
    }

    public function type(string $entityClass): object
    {
        $instance = new EntityType([
            'name' => $this->metadata->getEntity($entityClass)->getType
            'name' => str_replace('\\', '_', $entityClass) . '__' . $this->getConfig()->getGroup(),
            'description' => $this->metadata->getDocsForEntity($entityClass),
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);

        return $instance;
    }

    public function filter(string $entityClass): object
    {

    }

    public function resolve(string $entityClass): object
    {

    }

    public function getEntityManagerAlias(): string
    {
        return $this->entityManagerAlias;
    }

    public  function getConfig(): Config
    {
        return $this->config;
    }

    public  function getEntityManager(): EntityManager
    {
        return $this->container->get($this->getEntityManagerAlias());
    }
}
