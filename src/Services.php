<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use Doctrine\ORM\EntityManager;
use League\Event\EventDispatcher;
use Psr\Container\ContainerInterface;

/**
 * This class is used to remove complexity from the Driver class.
 * It doesn't change what the Driver does.  It just moves service bootstrapping
 * out of the Driver.
 */
class Services extends AbstractContainer
{
    /**
     * @param Driver        $driver             required
     * @param string        $entityManagerAlias required
     * @param Config        $config             required
     * @param Metadata\Metadata|null $metadata           optional so cached metadata can be loaded
     */
    public function __construct(
        Driver $driver,
        EntityManager $entityManager,
        ?Config $config = null,
        ?array $metadataConfig = null
    ) {
        $driver
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
                Type\TypeManager::class,
                static fn () => new Type\TypeManager()
            )
            ->set(
                Metadata\Metadata::class,
                static function (ContainerInterface $container) use ($metadataConfig) {
                    return (new Metadata\MetadataFactory($container, $metadataConfig))->getMetadata();
                }
            )
            ->set(
                Resolve\FieldResolver::class,
                static function (ContainerInterface $container) {
                    return new Resolve\FieldResolver(
                        $container->get(Config::class),
                        $container->get(Metadata\Metadata::class)
                    );
                }
            )
            ->set(
                Resolve\ResolveCollectionFactory::class,
                static function (ContainerInterface $container) {
                    return new Resolve\ResolveCollectionFactory(
                        $container->get(EntityManager::class),
                        $container->get(Config::class),
                        $container->get(Resolve\FieldResolver::class),
                        $container->get(Type\TypeManager::class)
                    );
                }
            )
            ->set(
                Resolve\ResolveEntityFactory::class,
                static function (ContainerInterface $container) {
                    return new Resolve\ResolveEntityFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(EventDispatcher::class)
                    );
                }
            )
            ->set(
                Criteria\CriteriaFactory::class,
                static function (ContainerInterface $container) {
                    return new Criteria\CriteriaFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(Type\TypeManager::class)
                    );
                }
            )
            ->set(
                Hydrator\HydratorFactory::class,
                static function (ContainerInterface $container) {
                    return new Hydrator\HydratorFactory(
                        $container->get(EntityManager::class),
                        $container->get(Metadata\Metadata::class)
                    );
                }
            )
            ->set(
                Input\InputFactory::class,
                static function (ContainerInterface $container) {
                    return new Input\InputFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(Type\TypeManager::class),
                        $container->get(Metadata\Metadata::class)
                    );
                }
            );
    }
}
