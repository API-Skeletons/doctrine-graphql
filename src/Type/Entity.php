<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Buildable;
use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Criteria\CriteriaFactory;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityDefinition;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;
use ApiSkeletons\Doctrine\GraphQL\Resolve\FieldResolver;
use ApiSkeletons\Doctrine\GraphQL\Resolve\ResolveCollectionFactory;
use ArrayObject;
use Closure;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ObjectType;
use Laminas\Hydrator\HydratorInterface;
use League\Event\EventDispatcher;

use function array_keys;
use function assert;
use function in_array;
use function ksort;

use const SORT_REGULAR;

class Entity implements Buildable
{
    /** @var mixed[]  */
    protected array $metadata;

    protected Config $config;

    protected CriteriaFactory $criteriaFactory;

    protected EntityManager $entityManager;

    protected EventDispatcher $eventDispatcher;

    protected FieldResolver $fieldResolver;

    protected HydratorFactory $hydratorFactory;

    protected ResolveCollectionFactory $collectionFactory;

    protected TypeManager $typeManager;

    /** @param mixed[] $params */
    public function __construct(AbstractContainer $container, string $typeName, array $params)
    {
        assert($container instanceof TypeManager);
        $container = $container->getContainer();

        $this->collectionFactory = $container->get(ResolveCollectionFactory::class);
        $this->config            = $container->get(Config::class);
        $this->criteriaFactory   = $container->get(CriteriaFactory::class);
        $this->entityManager     = $container->get(EntityManager::class);
        $this->eventDispatcher   = $container->get(EventDispatcher::class);
        $this->fieldResolver     = $container->get(FieldResolver::class);
        $this->hydratorFactory   = $container->get(HydratorFactory::class);
        $this->typeManager       = $container->get(TypeManager::class);

        if (! isset($container->get('metadata')[$typeName])) {
            throw new Error(
                'Entity ' . $typeName . ' is not mapped in the metadata',
            );
        }

        $this->metadata = $container->get('metadata')[$typeName];
    }

    public function __invoke(): ObjectType
    {
        return $this->getGraphQLType();
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydratorFactory->get($this->getEntityClass());
    }

    public function getTypeName(): string
    {
        return $this->metadata['typeName'];
    }

    public function getDescription(): string|null
    {
        return $this->metadata['description'];
    }

    /** @return mixed[] */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getEntityClass(): string
    {
        return $this->metadata['entityClass'];
    }

    /**
     * Build the type for the current entity
     *
     * @throws MappingException
     */
    protected function getGraphQLType(): ObjectType
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

        /**
         * If sortFields then resolve the fiels and sort them
         */
        if ($this->config->getSortFields()) {
            if ($arrayObject['fields'] instanceof Closure) {
                $arrayObject['fields'] = $arrayObject['fields']();
            }

            ksort($arrayObject['fields'], SORT_REGULAR);
        }

        /** @psalm-suppress InvalidArgument */
        $objectType = new ObjectType($arrayObject->getArrayCopy());
        $this->typeManager->set($this->getTypeName(), $objectType);

        return $objectType;
    }

    /** @param array<int, mixed[]> $fields */
    protected function addFields(array &$fields): void
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->getEntityClass());

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, array_keys($this->metadata['fields']))) {
                continue;
            }

            $fields[$fieldName] = [
                'type' => $this->typeManager
                    ->get($this->getmetadata()['fields'][$fieldName]['type']),
                'description' => $this->metadata['fields'][$fieldName]['description'],
            ];
        }
    }

    /** @param array<int, mixed[]> $fields */
    protected function addAssociations(array &$fields): void
    {
        $classMetadata = $this->entityManager->getClassMetadata($this->getEntityClass());

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (! in_array($associationName, array_keys($this->metadata['fields']))) {
                continue;
            }

            $associationMetadata = $classMetadata->getAssociationMapping($associationName);

            switch ($associationMetadata['type']) {
                case ClassMetadataInfo::ONE_TO_ONE:
                case ClassMetadataInfo::MANY_TO_ONE:
                case ClassMetadataInfo::TO_ONE:
                    $targetEntity             = $associationMetadata['targetEntity'];
                    $fields[$associationName] = function () use ($targetEntity) {
                        $entity = $this->typeManager->build(self::class, $targetEntity);

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
                        $entity    = $this->typeManager->build(self::class, $targetEntity);
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
                                    $this->metadata['fields'][$associationName],
                                ),
                                'pagination' => $this->typeManager->get('pagination'),
                            ],
                            'description' => $this->metadata['fields'][$associationName]['description'],
                            'resolve' => $this->collectionFactory->get($entity),
                        ];
                    };
                    break;
            }
        }
    }
}
