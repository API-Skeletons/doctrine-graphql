<?php

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Factory as CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Field\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Factory as MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\EntityFactory as ResolveEntityFactory;
use Doctrine\ORM\EntityManager;
use GraphQL\GraphQL;
use Psr\Container\ContainerInterface;

class Driver
{
    /**
     * @var ContainerInterface In order to allow for custom hydrators, a
     *                         container is available.
     */
    protected ?ContainerInterface $container;

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
     * @var ResolveEntityFactory
     */
    protected ResolveEntityFactory $resolveEntityFactory;

    /**
     * @var FieldResolver
     */
    protected FieldResolver $fieldResolver;

    /**
     * @param string $entityManagerAlias required
     * @param Config $config required
     * @param Metadata|null $metadata optional so cached metadata can be loaded
     */
    public function __construct(EntityManager $entityManager, ?Config $config = null, ?array $metadataConfig = null, ContainerInterface $container = null)
    {
        if (! $config) {
            $config = new Config();
        }

        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->config = $config;

        // Build the metadata from factory
        $metadataFactory = new MetadataFactory($this, $metadataConfig);
        $this->metadata = $metadataFactory->getMetadata();

        $this->criteria = new CriteriaFactory($this);
        $this->resolve = new ResolveEntityFactory($this);
        $this->fieldResolver = new FieldResolver($this);
    }

    public function getFieldResolver(): FieldResolver
    {
        return $this->fieldResolver;
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

    public function resolve(string $entityClass): \Closure
    {
        $resolve = $this->resolveEntityFactory;

        return $resolve($this->metadata->getEntity($entityClass));
    }

    public  function getConfig(): Config
    {
        return $this->config;
    }

    public  function getEntityManager(): EntityManager
    {
        return $this->entityManager;
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
