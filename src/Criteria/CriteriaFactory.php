<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Type\Between;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function array_filter;
use function array_keys;
use function assert;
use function in_array;

class CriteriaFactory
{
    protected EntityManager $entityManager;

    protected TypeManager $typeManager;

    public function __construct(EntityManager $entityManager, TypeManager $typeManager)
    {
        $this->entityManager = $entityManager;
        $this->typeManager = $typeManager;
    }

    /**
     * @param mixed[]|null $associationMetadata
     */
    public function get(
        Entity $targetEntity,
        ?Entity $owningEntity = null,
        ?string $associationName = null,
        ?array $associationMetadata = null
    ): InputObjectType {
        $typeName = $targetEntity->getTypeName() . '_' . $associationName . '_Filter';

        if ($this->typeManager->has($typeName)) {
            return $this->typeManager->get($typeName);
        }

        $filters         = [];
        $classMetadata   = $this->entityManager->getClassMetadata($targetEntity->getEntityClass());
        $graphQLMetadata = $targetEntity->getMetadataConfig();

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
//            'memberof',
        ];

        $allowedFilters = $allFilters;

        // Limit entity filters
        if ($graphQLMetadata['excludeCriteria']) {
            $excludeCriteria = $graphQLMetadata['excludeCriteria'];
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
            if (! in_array($fieldName, array_keys($graphQLMetadata['fields']))) {
                continue;
            }

            /**
             * @psalm-suppress UndefinedDocblockClass
             */
            $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            $graphQLType   = $this->typeManager->get($fieldMetadata['type']);

            if ($graphQLType && $classMetadata->isIdentifier($fieldName)) {
                $graphQLType = Type::id();
            }

            assert($graphQLType, 'GraphQL type not found for ' . $fieldMetadata['type']);

            // Step through all criteria and create filter fields
            $descriptions = [
                'eq' => 'Equals; same as name: value.  DateTime not supported.',
                'neq' => 'Not Equals',
                'lt' => 'Less Than',
                'lte' => 'Less Than or Equals',
                'gt' => 'Greater Than',
                'gte' => 'Greater Than or Equals',
            ];

            // Build simple filters
            foreach ($descriptions as $filter => $docs) {
                if (! in_array($filter, $allowedFilters)) {
                    continue;
                }

                $filters[$fieldName] = [
                    'name' => $fieldName . '_' . $filter,
                    'type' => $graphQLType,
                    'description' => $docs,
                ];
            }

            // eq filter is for field:value and field_eq:value
            if (in_array('eq', $allowedFilters)) {
                $filters[$fieldName] = [
                    'name' => $fieldName,
                    'type' => $graphQLType,
                    'description' => 'Equals.  DateTime not supported.',
                ];

                $filters[$fieldName . '_eq'] = [
                    'name' => $fieldName . '_eq',
                    'type' => $graphQLType,
                    'description' => 'Equals.  DateTime not supported.',
                ];
            }

            if (in_array('sort', $allowedFilters)) {
                $filters[$fieldName . '_sort'] = [
                    'name' => $fieldName . '_sort',
                    'type' => Type::string(),
                    'description' => 'Sort the result either ASC or DESC',
                ];
            }

            if (in_array('isnull', $allowedFilters)) {
                $fields[$fieldName . '_isnull'] = [
                    'name' => $fieldName . '_isnull',
                    'type' => Type::boolean(),
                    'description' => 'Takes a boolean.  If TRUE return results where the field is null. '
                        . 'If FALSE returns results where the field is not null. '
                        . 'Acts as "isEmpty" for collection filters.  A value of false will '
                        . 'be handled as though it were null.',
                ];
            }

            if (in_array('between', $allowedFilters)) {
                $fields[$fieldName . '_between'] = [
                    'name' => $fieldName . '_between',
                    'description' => 'Filter between `from` and `to` values.  Good substitute for DateTime Equals.',
                    'type' => new Between([
                        'fields' => [
                            'from' => [
                                'name' => 'from',
                                'type' => Type::nonNull($graphQLType),
                            ],
                            'to' => [
                                'name' => 'to',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ],
                    ]),
                ];
            }

            if (in_array('in', $allowedFilters)) {
                $fields[$fieldName . '_in'] = [
                    'name' => $fieldName . '_in',
                    'type' => Type::listOf($graphQLType),
                    'description' => 'Filter for values in an array',
                ];
            }

            if (in_array('notin', $allowedFilters)) {
                $fields[$fieldName . '_notin'] = [
                    'name' => $fieldName . '_notin',
                    'type' => Type::listOf($graphQLType),
                    'description' => 'Filter for values not in an array',
                ];
            }

            if ($graphQLType === Type::string()) {
                if (in_array('startswith', $allowedFilters)) {
                    $fields[$fieldName . '_startswith'] = [
                        'name' => $fieldName . '_startswith',
                        'type' => $graphQLType,
                        'documentation' => 'Strings only. '
                            . 'A like query from the beginning of the value `like \'value%\'`',
                    ];
                }

                if (in_array('endswith', $allowedFilters)) {
                    $fields[$fieldName . '_endswith'] = [
                        'name' => $fieldName . '_endswith',
                        'type' => $graphQLType,
                        'documentation' => 'Strings only. '
                            . 'A like query from the end of the value `like \'%value\'`',
                    ];
                }

                if (in_array('contains', $allowedFilters)) {
                    $fields[$fieldName . '_contains'] = [
                        'name' => $fieldName . '_contains',
                        'type' => $graphQLType,
                        'description' => 'Strings only. Similar to a Like query as `like \'%value%\'`',
                    ];
                }
            }

            /*
            if (in_array('memberof', $allowedFilters)) {
                $fields[$fieldName . '_memberof'] = [
                    'name' => $fieldName . '_memberof',
                    'type' => $graphQLType,
                    'description' => 'Matches a value in an array field.',
                ];
            }
            */
        }

        $fields['_skip']  = [
            'name' => '_skip',
            'type' => Type::int(),
            'documentation' => 'Skip x records from beginning of data set.',
        ];
        $fields['_limit'] = [
            'name' => '_limit',
            'type' => Type::int(),
            'documentation' => 'Limit the number of results.',
        ];

        $inputObject = new InputObjectType([
            'name' => $typeName,
            'fields' => static function () use ($fields) {
                return $fields;
            },
        ]);

        $this->typeManager->set($typeName, $inputObject);

        return $inputObject;
    }
}
