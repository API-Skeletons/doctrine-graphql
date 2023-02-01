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
use function in_array;
use function str_replace;
use function strlen;
use function strpos;
use function substr;

class MetadataFactory
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

    /** @param string[] $entityClasses */
    private function globalEnable(array $entityClasses): Metadata
    {
        $globalIgnore = $this->config->getGlobalIgnore();

        foreach ($entityClasses as $entityClass) {
            // Get extract by value or reference
            $byValue = $this->config->getGlobalByValue() ?? true;

            // Save entity-level metadata
            $this->metadataConfig[$entityClass] = [
                'entityClass' => $entityClass,
                'byValue' => $byValue,
                'namingStrategy' => null,
                'fields' => [],
                'filters' => [],
                'excludeCriteria' => [],
                'description' => $entityClass,
                'typeName' => $this->getTypeName($entityClass),
            ];

            // Fetch fields
            $entityClassMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($entityClass);
            $fieldNames          = $entityClassMetadata->getFieldNames();

            foreach ($fieldNames as $fieldName) {
                if (in_array($fieldName, $globalIgnore)) {
                    continue;
                }

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['description'] =
                    $fieldName;

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['type'] =
                    $entityClassMetadata->getTypeOfField($fieldName);

                // Set default strategy based on field type
                $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                    $this->getDefaultStrategy($entityClassMetadata->getTypeOfField($fieldName));

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['excludeCriteria'] = [];
            }

            // Fetch attributes for associations
            $associationNames = $this->entityManager->getMetadataFactory()
                ->getMetadataFor($entityClass)->getAssociationNames();

            foreach ($associationNames as $associationName) {
                if (in_array($associationName, $globalIgnore)) {
                    continue;
                }

                $this->metadataConfig[$entityClass]['fields'][$associationName]['excludeCriteria']         = [];
                $this->metadataConfig[$entityClass]['fields'][$associationName]['description']             = $associationName;
                $this->metadataConfig[$entityClass]['fields'][$associationName]['filterCriteriaEventName']
                    = null;

                // NullifyOwningAssociation is not used for globalEnable
                $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                    Strategy\AssociationDefault::class;
            }
        }

        $this->metadata = new Metadata($this->container, $this->metadataConfig);

        return $this->metadata;
    }

    protected function buildMetadata(): Metadata
    {
        $entityClasses = [];
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $entityClasses[] = $metadata->getName();
        }

        if ($this->config->getGlobalEnable()) {
            return $this->globalEnable($entityClasses);
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

    /**
     * Strip the configured entityPrefix from the type name
     */
    private function stripEntityPrefix(string $entityClass): string
    {
        if ($this->config->getEntityPrefix() !== null) {
            if (strpos($entityClass, $this->config->getEntityPrefix()) === 0) {
                $entityClass = substr($entityClass, strlen($this->config->getEntityPrefix()));
            }
        }

        return str_replace('\\', '_', $entityClass);
    }

    /**
     * Append the configured groupSuffix from the type name
     */
    private function appendGroupSuffix(string $entityClass): string
    {
        if ($this->config->getGroupSuffix() !== null) {
            if ($this->config->getGroupSuffix()) {
                $entityClass .= '_' . $this->config->getGroupSuffix();
            }
        } else {
            $entityClass .= '_' . $this->config->getGroup();
        }

        return $entityClass;
    }

    /**
     * Compute the GraphQL type name
     */
    private function getTypeName(string $entityClass): string
    {
        return $this->appendGroupSuffix($this->stripEntityPrefix($entityClass));
    }

    private function getDefaultStrategy(string|null $fieldType): string
    {
        // Set default strategy based on field type
        switch ($fieldType) {
            case 'tinyint':
            case 'smallint':
            case 'integer':
            case 'int':
                return Strategy\ToInteger::class;

            case 'boolean':
                return Strategy\ToBoolean::class;

            case 'decimal':
            case 'float':
                return Strategy\ToFloat::class;

            default:
                return Strategy\FieldDefault::class;
        }
    }
}
