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
     * By default all Driver instances share the same TypeManager.
     * The reason for this is because PageInfo is a required type name and
     * would collide if two drivers are used in the same schema.
     * To override this and use a non-shared local TypeManager, set this flag
     * before you instantiate the driver.
     */
    public static bool $clearTypeManager = false;

    /**
     * This is the shared TypeManger for all Drivers
     */
    private static Type\TypeManager|null $typeManagerShared = null;

    /**
     * A local persisting flag for the value of $clearTypeManager when
     * the Driver is created.
     */
    private bool $clearTypeManagerLocal = false;

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
        $this->clearTypeManagerLocal = self::$clearTypeManager;

        if (! self::$typeManagerShared) {
            self::$typeManagerShared = new Type\TypeManager();
        }

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
                },
            )
            ->set(
                EventDispatcher::class,
                static fn () => new EventDispatcher()
            )
            ->set(
                Type\TypeManager::class,
                fn () => $this->clearTypeManagerLocal ?
                        new Type\TypeManager()
                        : self::$typeManagerShared,
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
    }

    abstract public function set(string $id, mixed $value): mixed;
}
