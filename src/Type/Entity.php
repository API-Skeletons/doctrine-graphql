<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityDefinition;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use ArrayObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\ObjectType;
use Laminas\Hydrator\HydratorInterface;
use League\Event\EventDispatcher;
use Psr\Container\ContainerInterface;

use function array_keys;
use function in_array;

class Entity
{
//    protected Driver $driver;
    protected CriteriaFactory $criteriaFactory;

    protected EntityManager $entityManager;

    protected EventDispatcher $eventDispatcher;

    protected FieldResolver $fieldResolver;

    protected HydratorFactory $hydratorFactory;

    protected Metadata $metadata;

    protected ResolveCollectionFactory $collectionFactory;

    protected TypeManager $typeManager;

    /** @param mixed[] $metadataConfig */
    public function __construct(ContainerInterface $container, protected array $metadataConfig)
    {
        $this->collectionFactory = $container->get(ResolveCollectionFactory::class);
        $this->criteriaFactory   = $container->get(CriteriaFactory::class);
        $this->entityManager     = $container->get(EntityManager::class);
        $this->eventDispatcher   = $container->get(EventDispatcher::class);
        $this->fieldResolver     = $container->get(FieldResolver::class);
        $this->hydratorFactory   = $container->get(HydratorFactory::class);
        $this->metadata          = $container->get(Metadata::class);
        $this->typeManager       = $container->get(TypeManager::class);
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydratorFactory->get($this->getEntityClass());
    }

    public function getTypeName(): string
    {
        return $this->metadataConfig['typeName'];
    }

    public function getDescription(): string|null
    {
        return $this->metadataConfig['description'];
    }

    /** @return mixed[] */
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

        $fields = [];

        $this->addFields($fields);
        $this->addAssociations($fields);

        $arrayObject = new ArrayObject([
            'name' => $this->getTypeName(),
            'description' => $this->getDescription(),
            'fields' => static fn () => $fields,
            'resolveField' => $this->fieldResolver,
        ]);

        /**
         * Dispatch event to allow modifications to the ObjectType definition
         */
        $this->eventDispatcher->dispatch(
            new EntityDefinition($arrayObject, $this->getEntityClass() . '.definition'),
        );

        $objectType = new ObjectType($arrayObject->getArrayCopy());
        $this->typeManager->set($this->getTypeName(), $objectType);

        return $objectType;
    }

    /** @param array<int, mixed[]> $fields */
    protected function addFields(array &$fields): void
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->getEntityClass());

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, array_keys($this->metadataConfig['fields']))) {
                continue;
            }

            $fields[$fieldName] = [
                'type' => $this->typeManager
                    ->get($this->getMetadataConfig()['fields'][$fieldName]['type']),
                'description' => $this->metadataConfig['fields'][$fieldName]['description'],
            ];
        }
    }

    /** @param array<int, mixed[]> $fields */
    protected function addAssociations(array &$fields): void
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->getEntityClass());

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (! in_array($associationName, array_keys($this->metadataConfig['fields']))) {
                continue;
            }

            $associationMetadata = $classMetadata->getAssociationMapping($associationName);

            switch ($associationMetadata['type']) {
                case ClassMetadataInfo::ONE_TO_ONE:
                case ClassMetadataInfo::MANY_TO_ONE:
                case ClassMetadataInfo::TO_ONE:
                    $targetEntity             = $associationMetadata['targetEntity'];
                    $fields[$associationName] = function () use ($targetEntity) {
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
                    $targetEntity             = $associationMetadata['targetEntity'];
                    $fields[$associationName] = function () use ($targetEntity, $associationName) {
                        $entity    = $this->metadata->get($targetEntity);
                        $shortName = $this->getTypeName() . '_' . $associationName;

                        return [
                            'type' => $this->typeManager->build(
                                Connection::class,
                                $shortName . '_Connection',
                                $entity->getGraphQLType(),
                            ),
                            'args' => [
                                'filter' => $this->criteriaFactory->get(
                                    $entity,
                                    $this,
                                    $associationName,
                                    $this->metadataConfig['fields'][$associationName],
                                ),
                                'pagination' => $this->typeManager->get('pagination'),
                            ],
                            'description' => $this->metadataConfig['fields'][$associationName]['description'],
                            'resolve' => $this->collectionFactory->get($entity),
                        ];
                    };
                    break;
            }
        }
    }
}
