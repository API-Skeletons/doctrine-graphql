<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Laminas\Hydrator\HydratorInterface;
use Psr\Container\ContainerInterface;

use function array_keys;
use function in_array;

class Entity
{
//    protected Driver $driver;
    /** @var mixed[] */
    protected array $metadataConfig;

    protected ResolveCollectionFactory $collectionFactory;

    protected HydratorFactory $hydratorFactory;

    protected TypeManager $typeManager;

    protected EntityManager $entityManager;

    protected Metadata $metadata;

    protected FieldResolver $fieldResolver;

    protected CriteriaFactory $criteriaFactory;

    /**
     * @param mixed[] $metadataConfig
     */
    public function __construct(ContainerInterface $container, array $metadataConfig)
    {
        $this->collectionFactory = $container->get(ResolveCollectionFactory::class);
        $this->hydratorFactory   = $container->get(HydratorFactory::class);
        $this->typeManager       = $container->get(TypeManager::class);
        $this->entityManager     = $container->get(EntityManager::class);
        $this->metadata          = $container->get(Metadata::class);
        $this->fieldResolver     = $container->get(FieldResolver::class);
        $this->criteriaFactory   = $container->get(CriteriaFactory::class);
        $this->metadataConfig    = $metadataConfig;
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydratorFactory->get($this->getEntityClass());
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
        if ($this->typeManager->has($this->getTypeName())) {
            return $this->typeManager->get($this->getTypeName());
        }

        $classMetadata = $this->entityManager->getClassMetadata($this->getEntityClass());
        $graphQLFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, array_keys($this->metadataConfig['fields']))) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedDocblockClass
             */
            $fieldMapping              = $classMetadata->getFieldMapping($fieldName);

            if ($this->getMetadataConfig()['fields'][$fieldName]['type']) {
                $graphQLType = $this->typeManager
                    ->get($this->getMetadataConfig()['fields'][$fieldName]['type']);
            } else {
                $graphQLType = $this->typeManager->get($fieldMapping['type']);
            }

            $graphQLFields[$fieldName] = [
                'type' => $graphQLType,
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
                        $entity = $this->metadata->get($targetEntity);

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
                    $graphQLFields[$associationName] = function () use ($targetEntity, $associationName) {
                        $entity = $this->metadata->get($targetEntity);

                        return [
                            'type' => Type::listOf($entity->getGraphQLType()),
                            'args' => [
                                'filter' => $this->criteriaFactory->get(
                                    $entity,
                                    $this,
                                    $associationName,
                                    $this->metadataConfig['fields'][$associationName],
                                ),
                            ],
                            'description' => $this->metadataConfig['fields'][$associationName]['description'],
                            'resolve' => $this->collectionFactory->get($entity),
                        ];
                    };
                    break;
            }
        }

        $objectType = new ObjectType([
            'name' => $this->getTypeName(),
            'description' => $this->getDescription(),
            'fields' => static function () use ($graphQLFields) {
                return $graphQLFields;
            },
            'resolveField' => $this->fieldResolver,
        ]);

        $this->typeManager->set($this->getTypeName(), $objectType);

        return $objectType;
    }
}
