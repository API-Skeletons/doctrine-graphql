<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Attribute;
use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;
use ReflectionClass;

use function assert;
use function in_array;
use function str_replace;

class MetadataFactory
{
    protected ContainerInterface $container;
    protected ?Metadata $metadata;
    protected EntityManager $entityManager;
    protected Config $config;

    /** @var array|mixed[]|null */
    protected ?array $metadataConfig;

    /**
     * @param mixed|null $metadataConfig
     */
    public function __construct(ContainerInterface $container, ?array $metadataConfig)
    {
        $this->container      = $container;
        $this->metadataConfig = $metadataConfig;
        $this->entityManager  = $container->get(EntityManager::class);
        $this->config         = $container->get(Config::class);

        if ($metadataConfig) {
            $this->metadata = new Metadata($this->container, $metadataConfig);
        } else {
            $this->metadata = null;
        }
    }

    public function getMetadata(): Metadata
    {
        if ($this->metadata) {
            return $this->metadata;
        }

        return $this->buildMetadata();
    }

    /**
     * @param string[] $entityClasses
     */
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
                'typeName' => str_replace('\\', '_', $entityClass),
            ];

            // Append group to all type names
            $this->metadataConfig[$entityClass]['typeName'] .= '_' . $this->config->getGroup();

            // Fetch fields
            $entityClassMetadata = $this->entityManager
                ->getMetadataFactory()->getMetadataFor($entityClass);
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
            }

            // Fetch attributes for associations
            $associationNames = $this->entityManager->getMetadataFactory()
                ->getMetadataFor($entityClass)->getAssociationNames();

            foreach ($associationNames as $associationName) {
                if (in_array($associationName, $globalIgnore)) {
                    continue;
                }

                $this->metadataConfig[$entityClass]['fields'][$associationName]['description']     = $associationName;
                $this->metadataConfig[$entityClass]['fields'][$associationName]['excludeCriteria'] = [];

                $mapping = $entityClassMetadata->getAssociationMapping($associationName);

                // See comment on NullifyOwningAssociation for details of why this is done
                if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                    $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                        Strategy\NullifyOwningAssociation::class;
                } else {
                    $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                        Strategy\AssociationDefault::class;
                }
            }
        }

        $this->metadata = new Metadata($this->container, $this->metadataConfig);

        return $this->metadata;
    }

    protected function buildMetadata(): Metadata
    {
        // Get all entity classes
        $allMetadata = $this->entityManager
            ->getMetadataFactory()->getAllMetadata();

        $entityClasses = [];
        foreach ($allMetadata as $metadata) {
            $entityClasses[] = $metadata->getName();
        }

        if ($this->config->getGlobalEnable()) {
            return $this->globalEnable($entityClasses);
        }

        foreach ($entityClasses as $entityClass) {
            $entityInstance  = null;
            $reflectionClass = new ReflectionClass($entityClass);

            // Fetch attributes for the entity class filterd by Attribute\Entity
            foreach ($reflectionClass->getAttributes(Attribute\Entity::class) as $attribute) {
                $instance = $attribute->newInstance();

                // Only process attributes for the same group
                if ($instance->getGroup() !== $this->config->getGroup()) {
                    continue;
                }

                // Only one matching instance per group is allowed
                assert(
                    ! $entityInstance,
                    'Duplicate attribute found for entity '
                    . $entityClass . ', group ' . $instance->getGroup()
                );

                $entityInstance = $instance;

                // Get extract by value or reference
                $byValue = $this->config->getGlobalByValue() ?? $instance->getByValue();

                // Save entity-level metadata
                $this->metadataConfig[$entityClass] = [
                    'entityClass' => $entityClass,
                    'byValue' => $byValue,
                    'namingStrategy' => $instance->getNamingStrategy(),
                    'fields' => [],
                    'filters' => $instance->getFilters(),
                    'excludeCriteria' => $instance->getExcludeCriteria(),
                    'description' => $instance->getDescription(),
                    'typeName' => $instance->getTypeName()
                        ?: str_replace('\\', '_', $entityClass),
                ];

                // Append group to all type names
                $this->metadataConfig[$entityClass]['typeName'] .= '_' . $this->config->getGroup();
            }

            // Fetch attributes for fields
            $entityClassMetadata = $this->entityManager
                ->getMetadataFactory()->getMetadataFor($entityClass);
            $fieldNames          = $entityClassMetadata->getFieldNames();

            foreach ($fieldNames as $fieldName) {
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
                        . $fieldName . ', group ' . $instance->getGroup()
                    );
                    $fieldInstance = $instance;

                    $this->metadataConfig[$entityClass]['fields'][$fieldName]['description'] =
                        $instance->getDescription();

                    $this->metadataConfig[$entityClass]['fields'][$fieldName]['type'] =
                        $instance->getType() ?? $entityClassMetadata->getTypeOfField($fieldName);

                    if ($instance->getStrategy()) {
                        $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                            $instance->getStrategy();

                        continue;
                    }

                    // Set default strategy based on field type
                    $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                        $this->getDefaultStrategy($entityClassMetadata->getTypeOfField($fieldName));
                }
            }

            // Fetch attributes for associations
            $associationNames = $this->entityManager->getMetadataFactory()
                ->getMetadataFor($entityClass)->getAssociationNames();

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
                        . $associationName . ', group ' . $instance->getGroup()
                    );
                    $associationInstance = $instance;

                    $this->metadataConfig[$entityClass]['fields'][$associationName]['description']     = $instance->getDescription();
                    $this->metadataConfig[$entityClass]['fields'][$associationName]['excludeCriteria'] = $instance->getExcludeCriteria();

                    if ($instance->getStrategy()) {
                        $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] = $instance->getStrategy();

                        continue;
                    }

                    $mapping = $entityClassMetadata->getAssociationMapping($associationName);

                    // See comment on NullifyOwningAssociation for details of why this is done
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                        $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                            Strategy\NullifyOwningAssociation::class;
                    } else {
                        $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                            Strategy\AssociationDefault::class;
                    }
                }
            }
        }

        $this->metadata = new Metadata($this->container, $this->metadataConfig);

        return $this->metadata;
    }

    private function getDefaultStrategy(string $fieldType): string
    {
        // Set default strategy based on field type
        /**
         * @psalm-suppress UndefinedDocblockClass
         */
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

            case 'bigint':  // bigint is handled as a string internal to php
            case 'string':
            case 'text':
            case 'datetime':
            default:
                return Strategy\FieldDefault::class;
        }
    }
}
