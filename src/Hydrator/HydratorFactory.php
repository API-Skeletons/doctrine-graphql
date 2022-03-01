<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToJson;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\ORM\EntityManager;
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
class HydratorFactory extends AbstractContainer
{
    protected EntityManager $entityManager;

    protected Metadata $metadata;

    public function __construct(EntityManager $entityManager, Metadata $metadata)
    {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;

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

        $entity   = $this->metadata->get($id);
        $config   = $entity->getMetadataConfig();
        $hydrator = new DoctrineObject($this->entityManager, $config['byValue']);

        // Create field strategy and assign to hydrator
        if ($hydrator instanceof StrategyEnabledInterface) {
            foreach ($config['fields'] as $fieldName => $fieldMetadata) {
                assert(
                    in_array(StrategyInterface::class, class_implements($fieldMetadata['strategy'])),
                    'Strategy must implement ' . StrategyInterface::class
                );

                $hydrator->addStrategy($fieldName, $this->get($fieldMetadata['strategy']));
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
            $namingStrategyClass = $config['namingStrategy'];

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
