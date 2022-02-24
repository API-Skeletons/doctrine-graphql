<?php

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Hydrator\Factory as HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\Hydrator\HydratorInterface;

class Entity
{
    use Trait\Constructor;
    use Trait\GraphQLMapping;

    public function getDocs(): string
    {
        return $this->metadata['docs'];
    }

    /**
     * Build a hydrator for this entity
     *
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return (new HydratorFactory())($this->driver->getContainer(), $this->driver->getEntityManager(), $this);
    }

    /**
     * Return the raw data from the metadata.
     *
     * @return mixed
     */
    public function getMetadataContent(): array
    {
        return $this->metadata;
    }

    public function getTypeName(): string
    {
        return $this->metadata['typeName'];
    }

    public function getEntityClass(): string
    {
        return $this->metadata['entityClass'];
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
            if (in_array($fieldName, array_keys($this->metadata['strategies']))) {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
                $graphQLType = $this->mapFieldType($fieldMetadata['type']);
                assert($graphQLType, 'GraphQL Type not found for field ' . $fieldName);

                $graphQLFields[$fieldName] = [
                    'type' => $graphQLType,
                    'description' => $this->metadata['documentation'][$fieldName],
                ];
            }
        }

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (in_array($fieldName, array_keys($this->metadata['strategies']))) {
                $associationMetadata = $classMetadata->getFieldMapping($associationName);

                switch ($associationMetadata['type']) {
                    case ClassMetadataInfo::ONE_TO_ONE:
                    case ClassMetadataInfo::MANY_TO_ONE:
                    case ClassMetadataInfo::TO_ONE:
                        $targetEntity = $associationMetadata['targetEntity'];
                        $graphQLFields[$associationName] = function() use ($targetEntity) {

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

                            return [
                                'type' => Type::listOf($entity->getGraphQLType()),
                                'args' => [
                                    'filter' => $criteriaFilterManager->build($targetEntity, $options),
                                ],
                                    'resolve' => function (

                        };

                    default:
                        break;
                }
            }
        }
    }
}
