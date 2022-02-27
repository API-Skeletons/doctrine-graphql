<?php

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Type\Between;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Trait\GraphQLMapping;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class Factory
{
    use GraphQLMapping;

    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __invoke(Entity $entity): InputObjectType
    {
        $filters = [];
        $classMetadata = $this->driver->getEntityManager()
            ->getClassMetadata($entity->getEntityClass());
        $graphQLMetadata = $entity->getMetadataConfig();

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
            'memberof',
        ];

        // Limit filters
        if (!$graphQLMetadata['filters'] || $graphQLMetadata['filters'] === ['*']) {
            $allowedFilters = $allFilters;
        } else {
            $allowedFilters = $graphQLMetadata['filters'];
        }

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $graphQLType = null;

            /**
             * If there are no filters return empty type
             */
            if ($graphQLMetadata['filters'] === ['none']) {
                return new InputObjectType([
                    'name' => 'Filter',
                    'fields' => function () {
                        return [];
                    },
                ]);
            }

            // Only process fields which are in the graphql metadata
            if (!in_array($fieldName, array_keys($graphQLMetadata['strategies']))) {
                continue;
            }

            $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            $graphQLType = $this->mapFieldType($fieldMetadata['type']);

            if ($fieldMetadata['type'] === 'array') {
                $graphQLType = Type::string();
            }

            if ($graphQLType && $classMetadata->isIdentifier($fieldName)) {
                $graphQLType = Type::id();
            }

            // FIXME: Send event to allow overriding a field type

            if (!$graphQLType) {
                continue;
            }

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
                if (in_array($filter, $allowedFilters)) {
                    $filters[$fieldName] = [
                        'name' => $fieldName . '_' . $filter,
                        'type' => $graphQLType,
                        'description' => $docs,
                    ];
                }
            }

            // This eq filter is for field:value instead of field_eq:value
            if (in_array('eq', $allowedFilters)) {
                $filters[$fieldName] = [
                    'name' => $fieldName,
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
                    'type' => new Between(['fields' => [
                        'from' => [
                            'name' => 'from',
                            'type' => Type::nonNull($graphQLType),
                        ],
                        'to' => [
                            'name' => 'to',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]
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

            if ($graphQLType == Type::string()) {
                if (in_array('startswith', $allowedFilters)) {
                    $fields[$fieldName . '_startswith'] = [
                        'name' => $fieldName . '_startswith',
                        'type' => $graphQLType,
                        'documentation' => 'Strings only. '
                            . 'A like query from the beginning of the value `like \'value%\'`',
                    ];
                }

                if (in_array('endwith', $allowedFilters)) {
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

        $fields['_skip'] = [
            'name' => '_skip',
            'type' => Type::int(),
            'documentation' => 'Skip x records from beginning of data set.',
        ];
        $fields['_limit'] = [
            'name' => '_limit',
            'type' => Type::int(),
            'documentation' => 'Limit the number of results.',
        ];

        return new InputObjectType([
            'name' => 'Filter',
            'fields' => function () use ($fields) {
                return $fields;
            },
        ]);
    }
}
