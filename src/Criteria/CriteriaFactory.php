<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Criteria\Type\FiltersInputType;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityFilter;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ArrayObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use League\Event\EventDispatcher;

use function array_filter;
use function array_keys;
use function assert;
use function in_array;

class CriteriaFactory
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        protected TypeManager $typeManager,
        protected EventDispatcher $eventDispatcher,
    ) {
    }

    /** @param mixed[]|null $associationMetadata */
    public function get(
        Entity $targetEntity,
        Entity|null $owningEntity = null,
        string|null $associationName = null,
        array|null $associationMetadata = null,
    ): InputObjectType {
        if ($owningEntity) {
            $typeName = $owningEntity->getTypeName() . '_' . $associationName . '_filter';
        } else {
            $typeName = $targetEntity->getTypeName() . '_filter';
        }

        if ($this->typeManager->has($typeName)) {
            return $this->typeManager->get($typeName);
        }

        $fields         = [];
        $classMetadata  = $this->entityManager->getClassMetadata($targetEntity->getEntityClass());
        $entityMetadata = $targetEntity->getMetadataConfig();

        $allowedFilters = Filters::toArray();

        // Limit entity filters
        if ($entityMetadata['excludeCriteria']) {
            $excludeCriteria = $entityMetadata['excludeCriteria'];
            $allowedFilters  = array_filter($allowedFilters, static function ($value) use ($excludeCriteria) {
                return ! in_array($value, $excludeCriteria);
            });
        }

        // Limit association filters
        if ($associationName) {
            $excludeCriteria = $associationMetadata['excludeCriteria'];
            $allowedFilters  = array_filter($allowedFilters, static function ($value) use ($excludeCriteria) {
                return ! in_array($value, $excludeCriteria);
            });
        }

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $graphQLType = null;

            // Only process fields which are in the graphql metadata
            if (! in_array($fieldName, array_keys($entityMetadata['fields']))) {
                continue;
            }

            /** @psalm-suppress UndefinedDocblockClass */
            $fieldMetadata = $classMetadata->getFieldMapping($fieldName);

            $graphQLType = $this->typeManager
                ->get($entityMetadata['fields'][$fieldName]['type']);

            if ($graphQLType && $classMetadata->isIdentifier($fieldName)) {
                $graphQLType = Type::id();
            }

            assert($graphQLType, 'GraphQL type not found for ' . $fieldMetadata['type']);

            $fields[$fieldName] = [
                'name' => $fieldName,
                'type' => new FiltersInputType($typeName, $fieldName, $graphQLType, $allowedFilters),
                'description' => 'Filters for ' . $fieldName,
            ];

            if (in_array(Filters::SORT, $allowedFilters)) {
                $fields[$fieldName . '_sort'] = [
                    'name' => $fieldName . '_sort',
                    'type' => Type::string(),
                    'description' => 'Sort the result either ASC or DESC',
                ];
            }
        }

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            // Only process fields which are in the graphql metadata
            if (! in_array($associationName, array_keys($entityMetadata['fields']))) {
                continue;
            }

            /** @psalm-suppress UndefinedDocblockClass */
            $associationMetadata = $classMetadata->getAssociationMapping($associationName);
            $graphQLType         = Type::id();
            switch ($associationMetadata['type']) {
                case ClassMetadataInfo::ONE_TO_ONE:
                case ClassMetadataInfo::MANY_TO_ONE:
                case ClassMetadataInfo::TO_ONE:
                    // eq filter is for field:value and field_eq:value
                    if (in_array(Filters::EQ, $allowedFilters)) {
                        $fields[$associationName] = [
                            'name' => $associationName,
                            'type' => $graphQLType,
                            'description' => 'Equals.',
                        ];

                        $fields[$associationName . '_eq'] = [
                            'name' => $associationName . '_eq',
                            'type' => $graphQLType,
                            'description' => 'Equals.',
                        ];
                    }
            }
        }

        $arrayObject = new ArrayObject([
            'name' => $typeName,
            'fields' => static function () use ($fields) {
                return $fields;
            },
        ]);

        /**
         * Dispatch event to allow modifications to the ObjectType definition
         */
        $this->eventDispatcher->dispatch(
            new EntityFilter($arrayObject, $targetEntity->getEntityClass() . '.filter'),
        );

        /** @psalm-suppress InvalidArgument */
        $inputObject = new InputObjectType($arrayObject->getArrayCopy());

        $this->typeManager->set($typeName, $inputObject);

        return $inputObject;
    }
}
