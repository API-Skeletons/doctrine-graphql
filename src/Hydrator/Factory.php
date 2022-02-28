<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToJson;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use GraphQL\Error\Error;
use Laminas\Hydrator\Filter\FilterComposite;
use Laminas\Hydrator\Filter\FilterEnabledInterface;
use Laminas\Hydrator\Filter\FilterInterface;
use Laminas\Hydrator\NamingStrategy\NamingStrategyEnabledInterface;
use Laminas\Hydrator\NamingStrategy\NamingStrategyInterface;
use Laminas\Hydrator\Strategy\StrategyEnabledInterface;
use Laminas\Hydrator\Strategy\StrategyInterface;

use function assert;
use function class_implements;
use function in_array;

/**
 * This factory is used in the Metadata\Entity class to create a hydrator
 * for the current entity
 */
class Factory extends AbstractContainer
{
    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

        // Register project defaults
        $this
            ->set(AssociationDefault::class, new AssociationDefault())
            ->set(FieldDefault::class, new FieldDefault())
            ->set(NullifyOwningAssociation::class, new NullifyOwningAssociation())
            ->set(ToBoolean::class, new ToBoolean())
            ->set(ToFloat::class, new ToFloat())
            ->set(ToInteger::class, new ToInteger())
            ->set(ToJson::class, new ToJson())
            ->set(Password::class, new Password());
    }

    /**
     * @throws Error
     */
    public function get(string $id): mixed
    {
        // Custom hydrators should already be registered
        if ($this->has($id)) {
            return parent::get($id);
        }

        $entity   = $this->driver->getMetadata()->get($id);
        $config   = $entity->getMetadataConfig();
        $hydrator = new DoctrineObject($this->driver->getEntityManager(), $config['byValue']);

        // Create strategies and assign to hydrator
        if ($hydrator instanceof StrategyEnabledInterface) {
            foreach ($config['strategies'] as $fieldName => $strategyClass) {
                assert(
                    in_array(StrategyInterface::class, class_implements($strategyClass)),
                    'Strategy must implement ' . StrategyInterface::class
                );

                $hydrator->addStrategy($fieldName, $this->get($strategyClass));
            }
        }

        // Create filters and assign to hydrator
        if ($hydrator instanceof FilterEnabledInterface) {
            foreach ($config['filters'] as $name => $filterConfig) {
                // Default filters to AND
                $condition   = $filterConfig['condition'] ?? FilterComposite::CONDITION_AND;
                $filterClass = $filterConfig['filter'];
                assert(
                    in_array(FilterInterface::class, class_implements($filterClass)),
                    'Filter must implement ' . StrategyInterface::class
                );

                $hydrator->addFilter($name, $this->get($filterClass), $condition);
            }
        }

        // Create naming strategy and assign to hydrator
        if ($hydrator instanceof NamingStrategyEnabledInterface && $config['namingStrategy']) {
            $namingStrategyClass = $config['naming_strategy'];

            assert(
                in_array(NamingStrategyInterface::class, class_implements($namingStrategyClass)),
                'Naming Strategy must implement ' . NamingStrategyInterface::class
            );

            $hydrator->setNamingStrategy($this->get($namingStrategyClass));
        }

        $this->set($id, $hydrator);

         return $hydrator;
    }
}
