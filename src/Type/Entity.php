<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\CollectionFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\Hydrator\HydratorInterface;

use function array_keys;
use function in_array;

class Entity
{
    protected Driver $driver;
    /** @var mixed[] */
    protected array $metadataConfig;
    protected CollectionFactory $collectionFactory;

    /**
     * @param mixed[] $metadataConfig
     */
    public function __construct(Driver $driver, array $metadataConfig)
    {
        $this->driver            = $driver;
        $this->metadataConfig    = $metadataConfig;
        $this->collectionFactory = new CollectionFactory($this->driver);
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->driver->getHydratorFactory()->get($this->getEntityClass());
    }

    public function getTypeName(): string
    {
        return $this->metadataConfig['typeName'];
    }

    public function getDescription(): ?string
    {
        return $this->metadataConfig['description'];
    }

    /**
     * @return mixed[]
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
     * @throws MappingException
     */
    public function getGraphQLType(): ObjectType
    {
        if ($this->driver->getTypeManager()->has($this->getTypeName())) {
            return $this->driver->getTypeManager()->get($this->getTypeName());
        }

        $classMetadata = $this->driver->getEntityManager()
            ->getClassMetadata($this->getEntityClass());
        $graphQLFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, array_keys($this->metadataConfig['fields']))) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedDocblockClass
             */
            $fieldMapping              = $classMetadata->getFieldMapping($fieldName);
            $graphQLFields[$fieldName] = [
                'type' => $this->driver->getTypeManager()->get($fieldMapping['type']),
                'description' => $this->metadataConfig['fields'][$fieldName]['description'],
            ];
        }

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (! in_array($associationName, array_keys($this->metadataConfig['fields']))) {
                continue;
            }

            $associationMetadata = $classMetadata->getAssociationMapping($associationName);

            switch ($associationMetadata['type']) {
                case ClassMetadataInfo::ONE_TO_ONE:
                case ClassMetadataInfo::MANY_TO_ONE:
                case ClassMetadataInfo::TO_ONE:
                    $targetEntity                    = $associationMetadata['targetEntity'];
                    $graphQLFields[$associationName] = function () use ($targetEntity) {
                        $entity = $this->driver->getMetadata()->get($targetEntity);

                        return [
                            'type' => $entity->getGraphQLType(),
                            'description' => $entity->getDescription(),
                        ];
                    };
                    break;
                case ClassMetadataInfo::ONE_TO_MANY:
                case ClassMetadataInfo::MANY_TO_MANY:
                case ClassMetadataInfo::TO_MANY:
                    $targetEntity                    = $associationMetadata['targetEntity'];
                    $graphQLFields[$associationName] = function ()
 use ($targetEntity, $associationName) {
                        $entity = $this->driver->getMetadata()->get($targetEntity);

                        return [
                            'type' => Type::listOf($entity->getGraphQLType()),
                            'args' => [
                                'filter' => $this->driver->filter(
                                    $entity->getEntityClass(),
                                    $associationName,
                                    $this->metadataConfig['fields'][$associationName],
                                ),
                            ],
                            'description' => $this->metadataConfig['fields'][$associationName]['description'],
                            'resolve' => $this->collectionFactory->get($entity),
                        ];
                    };
                    break;

                default:
                    break;
            }
        }

        $objectType = new ObjectType([
            'name' => $this->getTypeName(),
            'description' => $this->getDescription(),
            'fields' => static function () use ($graphQLFields) {
                return $graphQLFields;
            },
            'resolveField' => $this->driver->getFieldResolver(),
        ]);

        $this->driver->getTypeManager()->set($this->getTypeName(), $objectType);

        return $objectType;
    }
}
