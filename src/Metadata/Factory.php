<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Attribute;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use ReflectionClass;

use function assert;
use function str_replace;

class Factory
{
    protected Driver $driver;
    protected ?Metadata $metadata;

    /** @var array|mixed[]|null */
    protected ?array $metadataConfig;

    /**
     * @param mixed|null $metadataConfig
     */
    public function __construct(Driver $driver, ?array $metadataConfig)
    {
        $this->driver         = $driver;
        $this->metadataConfig = $metadataConfig;

        if ($metadataConfig) {
            $this->metadata = new Metadata($driver, $metadataConfig);
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

    protected function buildMetadata(): Metadata
    {
        // Get all entity classes
        $allMetadata = $this->driver->getEntityManager()
            ->getMetadataFactory()->getAllMetadata();

        $entityClasses = [];
        foreach ($allMetadata as $metadata) {
            $entityClasses[] = $metadata->getName();
        }

        foreach ($entityClasses as $entityClass) {
            $entityInstance  = null;
            $reflectionClass = new ReflectionClass($entityClass);

            // Fetch attributes for the entity class filterd by Attribute\Entity
            foreach ($reflectionClass->getAttributes(Attribute\Entity::class) as $attribute) {
                $instance = $attribute->newInstance();

                // Only process attributes for the same group
                if ($instance->getGroup() !== $this->driver->getConfig()->getGroup()) {
                    continue;
                }

                // Only one matching instance per group is allowed
                assert(
                    ! $entityInstance,
                    'Duplicate attribute found for entity '
                    . $entityClass . ', group ' . $instance->getGroup()
                );

                $entityInstance = $instance;

                // Save entity-level metadata
                $this->metadataConfig[$entityClass] = [
                    'entityClass' => $entityClass,
                    'byValue' => $instance->getByValue(),
                    'namingStrategy' => $instance->getNamingStrategy(),
                    'fields' => [],
                    'filters' => $instance->getFilters(),
                    'excludeCriteria' => $instance->getExcludeCriteria(),
                    'description' => $instance->getDescription(),
                    'typeName' => $instance->getTypeName()
                        ?: str_replace('\\', '_', $entityClass),
                ];
            }

            // Fetch attributes for fields
            $entityClassMetadata = $this->driver->getEntityManager()
                ->getMetadataFactory()->getMetadataFor($entityClass);
            $fieldNames          = $entityClassMetadata->getFieldNames();

            foreach ($fieldNames as $fieldName) {
                $fieldInstance   = null;
                $reflectionField = $reflectionClass->getProperty($fieldName);

                foreach ($reflectionField->getAttributes(Attribute\Field::class) as $attribute) {
                    $instance = $attribute->newInstance();

                    // Only process attributes for the same group
                    if ($instance->getGroup() !== $this->driver->getConfig()->getGroup()) {
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

                    if ($instance->getStrategy()) {
                        $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                            $instance->getStrategy();

                        continue;
                    }

                    // Set default strategy based on field type
                    /**
                     * @psalm-suppress UndefinedDocblockClass
                     */
                    $fieldMetadata = $entityClassMetadata->getFieldMapping($fieldName);
                    switch ($fieldMetadata['type']) {
                        case 'tinyint':
                        case 'smallint':
                        case 'integer':
                        case 'int':
                            $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                                Strategy\ToInteger::class;
                            break;
                        case 'boolean':
                            $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                                Strategy\ToBoolean::class;
                            break;
                        case 'decimal':
                        case 'float':
                            $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                                Strategy\ToFloat::class;
                            break;
                        case 'bigint':  // bigint is handled as a string internal to php
                        case 'string':
                        case 'text':
                        case 'datetime':
                        default:
                            $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                                Strategy\FieldDefault::class;
                            break;
                    }
                }
            }

            // Fetch attributes for associations
            $associationNames = $this->driver->getEntityManager()->getMetadataFactory()
                ->getMetadataFor($entityClass)->getAssociationNames();

            foreach ($associationNames as $associationName) {
                $associationInstance   = null;
                $reflectionAssociation = $reflectionClass->getProperty($associationName);

                foreach ($reflectionAssociation->getAttributes(Attribute\Association::class) as $attribute) {
                    $instance = $attribute->newInstance();

                    // Only process attributes for the same group
                    if ($instance->getGroup() !== $this->driver->getConfig()->getGroup()) {
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

        $this->metadata = new Metadata($this->driver, $this->metadataConfig);

        return $this->metadata;
    }
}
