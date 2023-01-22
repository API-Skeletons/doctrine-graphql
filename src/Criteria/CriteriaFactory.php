<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Criteria\Type\Between;
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
            $typeName = $owningEntity->getTypeName() . '_' . $associationName . '_Filter';
        } else {
            $typeName = $targetEntity->getTypeName() . '_Filter';
        }

        if ($this->typeManager->has($typeName)) {
            return $this->typeManager->get($typeName);
        }

        $fields         = [];
        $classMetadata  = $this->entityManager->getClassMetadata($targetEntity->getEntityClass());
        $entityMetadata = $targetEntity->getMetadataConfig();

        $allFilters = [
            'sort',
            'eq',
            'neq',
            'lt',
            'lte',
            'gt',
            'gte',
            'isnull',
            'between',
            'in',
            'notin',
            'startswith',
            'endswith',
            'contains',
        ];

        $allowedFilters = $allFilters;

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

            // Step through all criteria and create filter fields
            $descriptions = [
                Filters::EQ  => 'Equals; same as name: value.  DateTime not supported.',
                Filters::NEQ => 'Not Equals',
                Filters::LT  => 'Less Than',
                Filters::LTE => 'Less Than or Equals',
                Filters::GT  => 'Greater Than',
                Filters::GTE => 'Greater Than or Equals',
            ];

            // Build simple filters
            foreach ($descriptions as $filter => $docs) {
                if (! in_array($filter, $allowedFilters)) {
                    continue;
                }

                $fields[$fieldName . '_' . $filter] = [
                    'name' => $fieldName . '_' . $filter,
                    'type' => $graphQLType,
                    'description' => $docs,
                ];
            }

            // eq filter is for field:value and field_eq:value
            if (in_array(Filters::EQ, $allowedFilters)) {
                $fields[$fieldName] = [
                    'name' => $fieldName,
                    'type' => $graphQLType,
                    'description' => 'Equals.  DateTime not supported.',
                ];
            }

            if (in_array(Filters::SORT, $allowedFilters)) {
                $fields[$fieldName . '_sort'] = [
                    'name' => $fieldName . '_sort',
                    'type' => Type::string(),
                    'description' => 'Sort the result either ASC or DESC',
                ];
            }

            if (in_array(Filters::ISNULL, $allowedFilters)) {
                $fields[$fieldName . '_isnull'] = [
                    'name' => $fieldName . '_isnull',
                    'type' => Type::boolean(),
                    'description' => 'Takes a boolean.  If TRUE return results where the field is null. '
                        . 'If FALSE returns results where the field is not null. '
                        . 'Acts as "isEmpty" for collection filters.  A value of false will '
                        . 'be handled as though it were null.',
                ];
            }

            if (in_array(Filters::BETWEEN, $allowedFilters)) {
                $fields[$fieldName . '_between'] = [
                    'name' => $fieldName . '_between',
                    'description' => 'Filter between `from` and `to` values.  Good substitute for DateTime Equals.',
                    'type' => new Between([
                        'fields' => [
                            'from' => [
                                'name' => 'from',
                                'type' => $graphQLType,
                            ],
                            'to' => [
                                'name' => 'to',
                                'type' => $graphQLType,
                            ],
                        ],
                    ]),
                ];
            }

            /** @psalm-suppress InvalidTemplateParam */
            if (in_array(Filters::IN, $allowedFilters)) {
                $fields[$fieldName . '_in'] = [
                    'name' => $fieldName . '_in',
                    'type' => Type::listOf($graphQLType),
                    'description' => 'Filter for values in an array',
                ];
            }

            /** @psalm-suppress InvalidTemplateParam */
            if (in_array(Filters::NOTIN, $allowedFilters)) {
                $fields[$fieldName . '_notin'] = [
                    'name' => $fieldName . '_notin',
                    'type' => Type::listOf($graphQLType),
                    'description' => 'Filter for values not in an array',
                ];
            }

            // According to GraphQL id types are represented as strings so
            // include string fitlers with ids
            if ($graphQLType !== Type::string() && $graphQLType !== Type::id()) {
                continue;
            }

            if (in_array(Filters::STARTSWITH, $allowedFilters)) {
                $fields[$fieldName . '_startswith'] = [
                    'name' => $fieldName . '_startswith',
                    'type' => $graphQLType,
                    'documentation' => 'Strings only. '
                        . 'A like query from the beginning of the value `like \'value%\'`',
                ];
            }

            if (in_array(Filters::ENDSWITH, $allowedFilters)) {
                $fields[$fieldName . '_endswith'] = [
                    'name' => $fieldName . '_endswith',
                    'type' => $graphQLType,
                    'documentation' => 'Strings only. '
                        . 'A like query from the end of the value `like \'%value\'`',
                ];
            }

            if (! in_array(Filters::CONTAINS, $allowedFilters)) {
                continue;
            }

            $fields[$fieldName . '_contains'] = [
                'name' => $fieldName . '_contains',
                'type' => $graphQLType,
                'description' => 'Strings only. Similar to a Like query as `like \'%value%\'`',
            ];
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
