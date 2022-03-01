<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveEntityFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use League\Event\EventDispatcher;

class Driver extends AbstractContainer
{
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

        $this
            // Plain classes
            ->set(EntityManager::class, $entityManager)
            ->set(Config::class, $config)
            ->set(EventDispatcher::class, new EventDispatcher())
            ->set(TypeManager::class, new TypeManager())
            ->set(Metadata::class, (new MetadataFactory($this, $metadataConfig))->getMetadata())

            // Composed classes
            ->set(FieldResolver::class,
                new FieldResolver($this->get(Config::class), $this->get(Metadata::class)))
            ->set(ResolveCollectionFactory::class,
                new ResolveCollectionFactory($this->get(Config::class), $this->get(FieldResolver::class)))
            ->set(ResolveEntityFactory::class,
                new ResolveEntityFactory(
                    $this->get(Config::class),
                    $this->get(EntityManager::class),
                    $this->get(EventDispatcher::class)
                ))
            ->set(CriteriaFactory::class,
                new CriteriaFactory($this->get(EntityManager::class), $this->get(TypeManager::class)))
            ->set(HydratorFactory::class,
                new HydratorFactory($this->get(EntityManager::class), $this->get(Metadata::class)))
            ;
    }

    public function type(string $entityClass): ObjectType
    {
        return $this->get(Metadata::class)->get($entityClass)->getGraphQLType();
    }

    public function filter(string $entityClass): object
    {
        return $this->get(CriteriaFactory::class)
            ->get($this->get(Metadata::class)->get($entityClass));
    }

    public function resolve(string $entityClass): Closure
    {
        return $this->get(ResolveEntityFactory::class)
            ->get($this->get(Metadata::class)->get($entityClass));
    }
}
