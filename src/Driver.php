<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Metadata\MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveEntityFactory;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use League\Event\EventDispatcher;
use Psr\Container\ContainerInterface;

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
            ->set(EntityManager::class, function() use ($entityManager) { return $entityManager; })
            ->set(Config::class, function () use ($config) { return $config; })

            ->set(
                EventDispatcher::class,
                fn() => new EventDispatcher()
            )

            ->set(
                TypeManager::class,
                fn() => new TypeManager()
            )

            ->set(
                Metadata::class,
                function (Driver $container) use ($metadataConfig) {
                    return (new MetadataFactory($container, $metadataConfig))->getMetadata();
                }
            )

            // Composed classes
            ->set(
                FieldResolver::class,
                function (ContainerInterface $container) {
                    return new FieldResolver(
                        $container->get(Config::class),
                        $container->get(Metadata::class)
                    );
                }
            )
            ->set(
                ResolveCollectionFactory::class,
                function (ContainerInterface $container) {
                    return new ResolveCollectionFactory(
                        $container->get(EntityManager::class),
                        $container->get(Config::class),
                        $container->get(FieldResolver::class),
                        $container->get(TypeManager::class)
                    );
                }
            )
            ->set(
                ResolveEntityFactory::class,
                function (ContainerInterface $container) {
                    return new ResolveEntityFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(EventDispatcher::class)
                    );
                }
            )
            ->set(
                CriteriaFactory::class,
                function( ContainerInterface $container) {
                    return new CriteriaFactory(
                        $container->get(EntityManager::class),
                        $container->get(TypeManager::class)
                    );
                }
            )
            ->set(
                HydratorFactory::class,
                function (ContainerInterface $container) {
                    return new HydratorFactory(
                        $container->get(EntityManager::class),
                        $container->get(Metadata::class)
                    );
                }
            );
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
