<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Factory as CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Factory as HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Factory as MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\EntityFactory as ResolveEntityFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Type\Manager as TypeManager;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use League\Event\EventDispatcher;

class Driver
{
    protected EntityManager $entityManager;

    /** @var Config The config must contain a `group` */
    protected Config $config;

    protected Metadata $metadata;

    protected CriteriaFactory $criteria;

    protected ResolveEntityFactory $resolveEntityFactory;

    protected FieldResolver $fieldResolver;

    protected HydratorFactory $hydratorFactory;

    protected TypeManager $typeManager;

    protected EventDispatcher $eventDispatcher;

    /**
     * @param string        $entityManagerAlias required
     * @param Config        $config             required
     * @param Metadata|null $metadata           optional so cached metadata can be loaded
     */
    public function __construct(EntityManager $entityManager, ?Config $config = null, ?array $metadataConfig = null)
    {
        if (! $config) {
            $config = new Config();
        }

        $metadataFactory = new MetadataFactory($this, $metadataConfig);

        $this->entityManager = $entityManager;
        $this->config        = $config;
        $this->metadata      = $metadataFactory->getMetadata();

        $this->criteria             = new CriteriaFactory($this);
        $this->resolveEntityFactory = new ResolveEntityFactory($this);
        $this->fieldResolver        = new FieldResolver($this);
        $this->hydratorFactory      = new HydratorFactory($this);
        $this->typeManager          = new TypeManager();
        $this->eventDispatcher      = new EventDispatcher();
    }

    public function getFieldResolver(): FieldResolver
    {
        return $this->fieldResolver;
    }

    public function type(string $entityClass): ObjectType
    {
        $entity = $this->metadata->get($entityClass);

        return $entity->getGraphQLType();
    }

    public function filter(
        string $entityClass,
        ?string $associationName = null,
        ?array $associationMetadata = null
    ): object {
        $criteria = $this->criteria;

        return $criteria($this->metadata->get($entityClass), $associationName, $associationMetadata);
    }

    public function resolve(string $entityClass): Closure
    {
        return $this->resolveEntityFactory->get($this->metadata->get($entityClass));
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

    public function getHydratorFactory(): HydratorFactory
    {
        return $this->hydratorFactory;
    }

    public function getTypeManager(): TypeManager
    {
        return $this->typeManager;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }
}
