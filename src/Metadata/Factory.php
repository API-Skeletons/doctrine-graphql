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
                    'hydrator' => $instance->getHydrator(),
                    'namingStrategy' => $instance->getNamingStrategy(),
                    'strategies' => [],
                    'filters' => $instance->getFilters(),
                    'documentation' => [],
                    'typeName' => $instance->getTypeName()
                        ?: str_replace('\\', '_', $entityClass),
                ];

                // Save documentation
                $this->metadataConfig[$entityClass]['documentation']['_entity'] = $instance->getDocs();
            }

            // Fetch attributes for fields
            $entityClassMetadata = $this->driver->getEntityManager()
                ->getMetadataFactory()->getMetadataFor($entityClass);
            $fieldNames          = $entityClassMetadata->getFieldNames();
            $fields              = [];

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

                    // Save documentation
                    $this->metadataConfig[$entityClass]['documentation'][$fieldName] = $instance->getDocs();

                    if ($instance->getStrategy()) {
                        $fields[$fieldName] = $instance->getStrategy();

                        continue;
                    }

                    // Set default strategies based on field type
                    $fieldMetadata = $entityClassMetadata->getFieldMapping($fieldName);
                    switch ($fieldMetadata['type']) {
                        case 'tinyint':
                        case 'smallint':
                        case 'integer':
                        case 'int':
                            $fields[$fieldName] = Strategy\ToInteger::class;
                            break;
                        case 'boolean':
                            $fields[$fieldName] = Strategy\ToBoolean::class;
                            break;
                        case 'decimal':
                        case 'float':
                            $fields[$fieldName] = Strategy\ToFloat::class;
                            break;
                        case 'bigint':  // bigint is handled as a string internal to php
                        case 'string':
                        case 'text':
                        case 'datetime':
                        default:
                            $fields[$fieldName] = Strategy\FieldDefault::class;
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
                    $fieldInstance = $instance;

                    // Save documentation
                    $this->metadataConfig[$entityClass]['documentation'][$associationName] = $instance->getDocs();

                    if ($instance->getStrategy()) {
                        $fields[$associationName] = $instance->getStrategy();

                        continue;
                    }

                    // Set default strategies based on association type
                    foreach ($associationNames as $associationName) {
                        $mapping = $entityClassMetadata->getAssociationMapping($associationName);

                        // See comment on NullifyOwningAssociation for details of why this is done
                        if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                            $fields[$associationName] = Strategy\NullifyOwningAssociation::class;
                        } else {
                            $fields[$associationName] = Strategy\AssociationDefault::class;
                        }
                    }
                }
            }

            if (! $fields) {
                continue;
            }

            $this->metadataConfig[$entityClass]['strategies'] = $fields;
        }

        $this->metadata = new Metadata($this->driver, $this->metadataConfig);

        return $this->metadata;
    }
}
