<?php

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Metadata\Factory as MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Criteria\Factory as CriteriaFactory;
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
     * @var EntityManager
     */
    protected EntityManager $entityManager;

    /**
     * @var Config The config must contain a `group`
     */
    protected Config $config;

    /**
     * @var Metadata
     */
    protected Metadata $metadata;

    /**
     * @var CriteriaFactory
     */
    protected CriteriaFactory $criteria;

    /**
     * @param string $entityManagerAlias required
     * @param Config $config required
     * @param Metadata|null $metadata optional so cached metadata can be loaded
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Config $config, ?Metadata $metadata = null)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->config = $config;

        // Build the metadata from factory
        $metadataFactory = new MetadataFactory($this);
        $this->metadata = $metadataFactory->getMetadata();

        $this->criteria = new CriteriaFactory($this);
    }

    public function type(string $entityClass): object
    {
        $entity = $this->metadata->getEntity($entityClass);

        return $entity->getGraphQLType();
    }

    public function filter(string $entityClass): object
    {
        $criteria = $this->criteria;

        return $criteria($this->metadata->getEntity($entityClass));
    }

    public function resolve(string $entityClass): object
    {

    }

    public  function getConfig(): Config
    {
        return $this->config;
    }

    public  function getEntityManager(): EntityManager
    {
        return $this->entityManager);
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
