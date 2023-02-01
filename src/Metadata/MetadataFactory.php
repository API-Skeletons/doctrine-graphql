<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Attribute;
use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;
use ReflectionClass;

use function assert;

class MetadataFactory extends AbstractMetadataFactory
{
    protected Metadata|null $metadata = null;
    protected EntityManager $entityManager;
    protected Config $config;

    /** @param mixed|null $metadataConfig */
    public function __construct(protected ContainerInterface $container, protected array|null $metadataConfig)
    {
        $this->entityManager = $container->get(EntityManager::class);
        $this->config        = $container->get(Config::class);

        if (empty($metadataConfig)) {
            return;
        }

        $this->metadata = new Metadata($this->container, $metadataConfig);
    }

    public function getMetadata(): Metadata
    {
        if ($this->metadata) {
            return $this->metadata;
        }

        return $this->buildMetadata();
    }

    protected function buildMetadata(): Metadata
    {
        $entityClasses = [];
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $entityClasses[] = $metadata->getName();
        }

        if ($this->config->getGlobalEnable()) {
            $globalEnable = $this->container->get(GlobalEnable::class);

            return new Metadata($this->container, $globalEnable($entityClasses));
        }

        foreach ($entityClasses as $entityClass) {
            $reflectionClass     = new ReflectionClass($entityClass);
            $entityClassMetadata = $this->entityManager
                ->getMetadataFactory()->getMetadataFor($reflectionClass->getName());

            $this->buildMetadataConfigForEntity($reflectionClass);
            $this->buildMetadataConfigForFields($entityClassMetadata, $reflectionClass);
            $this->buildMetadataConfigForAssociations($entityClassMetadata, $reflectionClass);
        }

        $this->metadata = new Metadata($this->container, $this->metadataConfig);

        return $this->metadata;
    }

    /**
     * Using the entity class attributes, generate the metadataConfig.
     * The buildMetadataConfig* functions exist to simplify the buildMetadata
     * function.
     */
    private function buildMetadataConfigForEntity(ReflectionClass $reflectionClass): void
    {
        $entityInstance = null;

        // Fetch attributes for the entity class filterd by Attribute\Entity
        foreach ($reflectionClass->getAttributes(Attribute\Entity::class) as $attribute) {
            $instance = $attribute->newInstance();

            // Only process attributes for the Config group
            if ($instance->getGroup() !== $this->config->getGroup()) {
                continue;
            }

            // Only one matching instance per group is allowed
            assert(
                ! $entityInstance,
                'Duplicate attribute found for entity '
                . $reflectionClass->getName() . ', group ' . $instance->getGroup(),
            );
            $entityInstance = $instance;

            // Save entity-level metadata
            $this->metadataConfig[$reflectionClass->getName()] = [
                'entityClass' => $reflectionClass->getName(),
                'byValue' => $this->config->getGlobalByValue() ?? $instance->getByValue(),
                'namingStrategy' => $instance->getNamingStrategy(),
                'fields' => [],
                'filters' => $instance->getFilters(),
                'excludeCriteria' => $instance->getExcludeCriteria(),
                'description' => $instance->getDescription(),
                'typeName' => $instance->getTypeName()
                    ? $this->appendGroupSuffix($instance->getTypeName()) :
                    $this->getTypeName($reflectionClass->getName()),
            ];
        }
    }

    private function buildMetadataConfigForFields(
        ClassMetadata $entityClassMetadata,
        ReflectionClass $reflectionClass,
    ): void {
        foreach ($entityClassMetadata->getFieldNames() as $fieldName) {
            $fieldInstance   = null;
            $reflectionField = $reflectionClass->getProperty($fieldName);

            foreach ($reflectionField->getAttributes(Attribute\Field::class) as $attribute) {
                $instance = $attribute->newInstance();

                // Only process attributes for the same group
                if ($instance->getGroup() !== $this->config->getGroup()) {
                    continue;
                }

                // Only one matching instance per group is allowed
                assert(
                    ! $fieldInstance,
                    'Duplicate attribute found for field '
                    . $fieldName . ', group ' . $instance->getGroup(),
                );
                $fieldInstance = $instance;

                $this->metadataConfig[$reflectionClass->getName()]['fields'][$fieldName]['description'] =
                    $instance->getDescription();

                $this->metadataConfig[$reflectionClass->getName()]['fields'][$fieldName]['type'] =
                    $instance->getType() ?? $entityClassMetadata->getTypeOfField($fieldName);

                if ($instance->getStrategy()) {
                    $this->metadataConfig[$reflectionClass->getName()]['fields'][$fieldName]['strategy'] =
                        $instance->getStrategy();

                    continue;
                }

                $this->metadataConfig[$reflectionClass->getName()]['fields'][$fieldName]['excludeCriteria'] =
                    $instance->getExcludeCriteria();

                // Set default strategy based on field type
                $this->metadataConfig[$reflectionClass->getName()]['fields'][$fieldName]['strategy'] =
                    $this->getDefaultStrategy($entityClassMetadata->getTypeOfField($fieldName));
            }
        }
    }

    private function buildMetadataConfigForAssociations(
        ClassMetadata $entityClassMetadata,
        ReflectionClass $reflectionClass,
    ): void {
        // Fetch attributes for associations
        $associationNames = $this->entityManager->getMetadataFactory()
            ->getMetadataFor($reflectionClass->getName())->getAssociationNames();

        foreach ($associationNames as $associationName) {
            $associationInstance   = null;
            $reflectionAssociation = $reflectionClass->getProperty($associationName);

            foreach ($reflectionAssociation->getAttributes(Attribute\Association::class) as $attribute) {
                $instance = $attribute->newInstance();

                // Only process attributes for the same group
                if ($instance->getGroup() !== $this->config->getGroup()) {
                    continue;
                }

                // Only one matching instance per group is allowed
                assert(
                    ! $associationInstance,
                    'Duplicate attribute found for association '
                    . $associationName . ', group ' . $instance->getGroup(),
                );
                $associationInstance = $instance;

                $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['description']             =
                    $instance->getDescription();
                $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['excludeCriteria']         =
                    $instance->getExcludeCriteria();
                $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['filterCriteriaEventName'] =
                    $instance->getFilterCriteriaEventName();

                if ($instance->getStrategy()) {
                    $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['strategy']
                        = $instance->getStrategy();

                    continue;
                }

                $mapping = $entityClassMetadata->getAssociationMapping($associationName);

                // See comment on NullifyOwningAssociation for details of why this is done
                if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                    $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['strategy'] =
                        Strategy\NullifyOwningAssociation::class;
                } else {
                    $this->metadataConfig[$reflectionClass->getName()]['fields'][$associationName]['strategy'] =
                        Strategy\AssociationDefault::class;
                }
            }
        }
    }
}
