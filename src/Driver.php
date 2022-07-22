<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Input\InputFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Metadata\MetadataFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveEntityFactory;
use ApiSkeletons\Doctrine\GraphQL\Type\Connection;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Closure;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\InputObjectType;
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
        $this
            // Plain classes
            ->set(EntityManager::class, $entityManager)
            ->set(
                Config::class,
                static function () use ($config) {
                    if (! $config) {
                        $config = new Config();
                    }

                    return $config;
                }
            )
            ->set(
                EventDispatcher::class,
                static fn () => new EventDispatcher()
            )
            ->set(
                TypeManager::class,
                static fn () => new TypeManager()
            )
            ->set(
                Metadata::class,
                static function (ContainerInterface $container) use ($metadataConfig) {
                    return (new MetadataFactory($container, $metadataConfig))->getMetadata();
                }
            )
            ->set(
                FieldResolver::class,
                static function (ContainerInterface $container) {
                    return new FieldResolver(
                        $container->get(Config::class),
                        $container->get(Metadata::class)
                    );
                }
            )
            ->set(
                ResolveCollectionFactory::class,
                static function (ContainerInterface $container) {
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
                static function (ContainerInterface $container) {
                    return new ResolveEntityFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(EventDispatcher::class)
                    );
                }
            )
            ->set(
                CriteriaFactory::class,
                static function (ContainerInterface $container) {
                    return new CriteriaFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(TypeManager::class)
                    );
                }
            )
            ->set(
                HydratorFactory::class,
                static function (ContainerInterface $container) {
                    return new HydratorFactory(
                        $container->get(EntityManager::class),
                        $container->get(Metadata::class)
                    );
                }
            )
            ->set(
                InputFactory::class,
                static function (ContainerInterface $container) {
                    return new InputFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(TypeManager::class),
                        $container->get(Metadata::class)
                    );
                }
            )
            ->set(
                Connection::class,
                static fn () => new Connection()
            );
    }

    public function connection(ObjectType $objectType): ObjectType
    {
        return $this->get(Connection::class)->get($objectType);
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

    /**
     * @param string[] $requiredFields An optional list of just the required fields you want for the mutation.
     *                              This allows specific fields per mutation.
     * @param string[] $optionalFields An optional list of optional fields you want for the mutation.
     *                              This allows specific fields per mutation.
     */
    public function input(string $entityClass, array $requiredFields = [], array $optionalFields = []): InputObjectType
    {
        return $this->get(InputFactory::class)->get($entityClass, $requiredFields, $optionalFields);
    }
}
