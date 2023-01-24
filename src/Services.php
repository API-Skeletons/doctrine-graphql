<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use Doctrine\ORM\EntityManager;
use League\Event\EventDispatcher;
use Psr\Container\ContainerInterface;

/**
 * This trait is used to remove complexity from the Driver class.
 * It doesn't change what the Driver does.  It just separates the container work
 * from the GraphQL Driver.
 */
trait Services
{
    /**
     * This is the shared TypeManger for all Drivers
     */
    private static Type\TypeManager|null $typeManagerShared = null;

    /**
     * @param string                 $entityManagerAlias required
     * @param Config                 $config             required
     * @param Metadata\Metadata|null $metadata           optional so cached metadata can be loaded
     */
    public function __construct(
        EntityManager $entityManager,
        Config|null $config = null,
        array|null $metadataConfig = null,
    ) {
        if (! $config) {
            $config = new Config();
        }

        $this
            // Plain classes
            ->set(EntityManager::class, $entityManager)
            ->set(
                Config::class,
                static function () use ($config) {
                    return $config;
                },
            )
            ->set(
                EventDispatcher::class,
                static fn () => new EventDispatcher()
            )
            ->set(
                Type\TypeManager::class,
                static function (ContainerInterface $container) {
                    if (! $container->get(Config::class)->getSharedTypeManager()) {
                        return new Type\TypeManager();
                    }

                    if (! self::$typeManagerShared) {
                        self::$typeManagerShared = new Type\TypeManager();
                    }

                    return self::$typeManagerShared;
                },
            )
            ->set(
                Metadata\Metadata::class,
                static function (ContainerInterface $container) use ($metadataConfig) {
                    return (new Metadata\MetadataFactory($container, $metadataConfig))->getMetadata();
                },
            )
            ->set(
                Resolve\FieldResolver::class,
                static function (ContainerInterface $container) {
                    return new Resolve\FieldResolver(
                        $container->get(Config::class),
                        $container->get(Metadata\Metadata::class),
                    );
                },
            )
            ->set(
                Resolve\ResolveCollectionFactory::class,
                static function (ContainerInterface $container) {
                    return new Resolve\ResolveCollectionFactory(
                        $container->get(EntityManager::class),
                        $container->get(Config::class),
                        $container->get(Resolve\FieldResolver::class),
                        $container->get(Type\TypeManager::class),
                    );
                },
            )
            ->set(
                Resolve\ResolveEntityFactory::class,
                static function (ContainerInterface $container) {
                    return new Resolve\ResolveEntityFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(EventDispatcher::class),
                    );
                },
            )
            ->set(
                Criteria\CriteriaFactory::class,
                static function (ContainerInterface $container) {
                    return new Criteria\CriteriaFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(Type\TypeManager::class),
                        $container->get(EventDispatcher::class),
                    );
                },
            )
            ->set(
                Hydrator\HydratorFactory::class,
                static function (ContainerInterface $container) {
                    return new Hydrator\HydratorFactory(
                        $container->get(EntityManager::class),
                        $container->get(Metadata\Metadata::class),
                    );
                },
            )
            ->set(
                Input\InputFactory::class,
                static function (ContainerInterface $container) {
                    return new Input\InputFactory(
                        $container->get(Config::class),
                        $container->get(EntityManager::class),
                        $container->get(Type\TypeManager::class),
                        $container->get(Metadata\Metadata::class),
                    );
                },
            );

        if (! $this->get(Config::class)->getGlobalEnable()) {
            return;
        }

        $this->set(Type\TypeManager::class, new Type\TypeManager());
    }

    abstract public function set(string $id, mixed $value): mixed;
}
