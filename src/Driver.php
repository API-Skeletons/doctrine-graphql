<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Factory as CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Field\Resolver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Factory as HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Factory as MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\EntityFactory as ResolveEntityFactory;
use ApiSkeletons\Doctrine\GraphQL\Type\DateTime;
use ApiSkeletons\Doctrine\GraphQL\Type\Manager as TypeManager;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use Psr\Container\ContainerInterface;

class Driver
{
    /**
     * @var ContainerInterface In order to allow for custom hydrators, a
     *                         container is available.
     */
    protected ?ContainerInterface $container;

    protected EntityManager $entityManager;

    /** @var Config The config must contain a `group` */
    protected Config $config;

    protected Metadata $metadata;

    protected CriteriaFactory $criteria;

    protected ResolveEntityFactory $resolveEntityFactory;

    protected Resolver $fieldResolver;

    protected HydratorFactory $hydratorFactory;

    /**
     * @param string        $entityManagerAlias required
     * @param Config        $config             required
     * @param Metadata|null $metadata           optional so cached metadata can be loaded
     */
    public function __construct(EntityManager $entityManager, ?Config $config = null, ?array $metadataConfig = null, ?ContainerInterface $container = null)
    {
        if (! $config) {
            $config = new Config();
        }

        $this->container     = $container;
        $this->entityManager = $entityManager;
        $this->config        = $config;

        // Build the metadata from factory
        $metadataFactory = new MetadataFactory($this, $metadataConfig);
        $this->metadata  = $metadataFactory->getMetadata();

        $this->criteria             = new CriteriaFactory($this);
        $this->resolveEntityFactory = new ResolveEntityFactory($this);
        $this->fieldResolver        = new Resolver($this);
        $this->hydratorFactory      = new HydratorFactory($this);

        // Set static types
        TypeManager::set('datetime', new DateTime());
    }

    public function getFieldResolver(): Resolver
    {
        return $this->fieldResolver;
    }

    public function type(string $entityClass): ObjectType
    {
        $entity = $this->metadata->getEntity($entityClass);

        return $entity->getGraphQLType();
    }

    public function filter(string $entityClass): object
    {
        $criteria = $this->criteria;

        return $criteria($this->metadata->getEntity($entityClass));
    }

    public function resolve(string $entityClass): Closure
    {
        return $this->resolveEntityFactory->get($this->metadata->getEntity($entityClass));
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getEntityManager(): EntityManager
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

    public function getHydratorFactory(): HydratorFactory
    {
        return $this->hydratorFactory;
    }
}
