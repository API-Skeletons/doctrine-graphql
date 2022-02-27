<?php

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Factory as HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;
use ApiSkeletons\Doctrine\GraphQL\Resolve\CollectionFactory;
use ApiSkeletons\Doctrine\GraphQL\Type\Manager as TypeManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\Hydrator\HydratorInterface;

class Entity
{
    use Trait\GraphQLMapping;

    protected Driver $driver;

    protected array $metadataConfig;

    protected CollectionFactory $collectionFactory;

    public function __construct(Driver $driver, array $metadataConfig)
    {
        $this->driver = $driver;
        $this->metadataConfig = $metadataConfig;
        $this->collectionFactory = new CollectionFactory($this->driver);
    }

    public function getHydrator(): HydratorInterface
    {
        $hydratorFactory = new HydratorFactory($this->driver);

        return $hydratorFactory->get($this);
    }

    public function getTypeName(): string
    {
        return $this->metadataConfig['typeName'];
    }

    public function getDocs(): string
    {
        return $this->metadataConfig['documentation']['_entity'];
    }

    /**
     * @return mixed
     */
    public function getMetadataConfig(): array
    {
        return $this->metadataConfig;
    }

    public function getEntityClass(): string
    {
        return $this->metadataConfig['entityClass'];
    }

    /**
     * Build the type for the current entity
     *
     * @return ObjectType
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getGraphQLType(): ObjectType
    {
        if (TypeManager::has($this->getTypeName())) {
            return TypeManager::get($this->getTypeName());
        }

        $classMetadata = $this->driver->getEntityManager()
            ->getClassMetadata($this->getEntityClass());
        $graphQLFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, array_keys($this->metadataConfig['strategies']))) {
                $graphQLFields[$fieldName] = [
                    'type' => $this->mapFieldType(
                        $classMetadata->getFieldMapping($fieldName)['type']
                    ),
                    'description' => $this->metadataConfig['documentation'][$fieldName],
                ];
            }
        }

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (in_array($associationName, array_keys($this->metadataConfig['strategies']))) {
                $associationMetadata = $classMetadata->getAssociationMapping($associationName);

                switch ($associationMetadata['type']) {
                    case ClassMetadataInfo::ONE_TO_ONE:
                    case ClassMetadataInfo::MANY_TO_ONE:
                    case ClassMetadataInfo::TO_ONE:
                        $targetEntity = $associationMetadata['targetEntity'];
                        $graphQLFields[$associationName] = function () use ($targetEntity) {
                            $entity = $this->driver->getMetadata()->getEntity($targetEntity);

                            return [
                                'type' => $entity->getGraphQLType(),
                                'description' => $entity->getDocs(),
                            ];
                        };
                        break;
                    case ClassMetadataInfo::ONE_TO_MANY:
                    case ClassMetadataInfo::MANY_TO_MANY:
                    case ClassMetadataInfo::TO_MANY:
                        $targetEntity = $associationMetadata['targetEntity'];
                        $graphQLFields[$associationName] = function () use ($targetEntity) {
                            $entity = $this->driver->getMetadata()->getEntity($targetEntity);

                            return [
                                'type' => Type::listOf($entity->getGraphQLType()),
                                'args' => [
                                    'filter' => $this->driver->filter($entity->getEntityClass()),
                                ],
                                'resolve' => $this->collectionFactory->get($entity),
                            ];
                        };

                    default:
                        break;
                }
            }
        }

        $objectType = new ObjectType([
            'name' => $this->getTypeName(),
            'description' => $this->getDocs(),
            'fields' => function () use ($graphQLFields) {
                return $graphQLFields;
            },
            'resolveField' => $this->driver->getFieldResolver(),
        ]);

        TypeManager::set($this->getTypeName(), $objectType);

        return $objectType;
    }
}
