<?php

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Factory as HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;
use ApiSkeletons\Doctrine\GraphQL\Resolve\CollectionFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\Hydrator\HydratorInterface;

class Entity
{
    use Trait\GraphQLMapping;

    protected Driver $driver;

    protected array $metadataConfig;

    public function __construct(Driver $driver, array $metadataConfig)
    {
        $this->driver = $driver;
        $this->metadataConfig = $metadataConfig;
    }

    public function getHydrator(): HydratorInterface
    {
        $hydratorFactory = new HydratorFactory($this->driver);

        return $hydratorFactory($this);
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
        $classMetadata = $this->driver->getEntityManager()
            ->getClassMetadata($this->getEntityClass());

        $graphQLFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, array_keys($this->metadataConfig['strategies']))) {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
                $graphQLType = $this->mapFieldType($fieldMetadata['type']);

                $graphQLFields[$fieldName] = [
                    'type' => $graphQLType,
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

                            // getGraphQLType should use a type manager/service provider?

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
                            $collectionResolve = new CollectionFactory($this->driver);

                            return [
                                'type' => Type::listOf($entity->getGraphQLType()),
                                'args' => [
                                    'filter' => $this->driver->filter($entity->getEntityClass()),
                                ],
                                'resolve' => $collectionResolve($entity),
                            ];
                        };

                    default:
                        break;
                }
            }
        }

        return new ObjectType([
            'name' => $this->metadataConfig['typeName'],
            'description' => $this->getDocs(),
            'fields' => function () use ($graphQLFields) {
                return $graphQLFields;
            },
            'resolveField' => $this->driver->getFieldResolver(),
        ]);
    }
}
