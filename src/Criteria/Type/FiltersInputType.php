<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Filters as FiltersDef;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class FiltersInputType extends InputObjectType
{
    public function __construct(string $typeName, string $fieldName, Type $type, array $allowedFilters)
    {
        $configuration = [
            'name' => 'filters',
            'description' => 'Field filters',
            'fields' => []
        ];

        if (in_array(FiltersDef::EQ, $allowedFilters)) {
            $configuration['fields'][FiltersDef::EQ] = [
                'name' => FiltersDef::EQ,
                'type' => $type,
                'description' => 'Equals. DateTime not supported.',
            ];
        }

        if (in_array(FiltersDef::NEQ, $allowedFilters)) {
            $configuration['fields'][FiltersDef::NEQ] = [
                'name' => FiltersDef::NEQ,
                'type'        => $type,
                'description' => 'Not equals',
            ];
        }

        if (in_array(FiltersDef::LT, $allowedFilters)) {
            $configuration['fields'][FiltersDef::LT] = [
                'name' => FiltersDef::LT,
                'type'        => $type,
                'description' => 'Less than',
            ];
        }

        if (in_array(FiltersDef::LTE, $allowedFilters)) {
            $configuration['fields'][FiltersDef::LTE] = [
                'name' => FiltersDef::LTE,
                'type'        => $type,
                'description' => 'Less than or equals',
            ];
        }

        if (in_array(FiltersDef::GT, $allowedFilters)) {
            $configuration['fields'][FiltersDef::GT] = [
                'name' => FiltersDef::GT,
                'type'        => $type,
                'description' => 'Greater than',
            ];
        }

        if (in_array(FiltersDef::GTE, $allowedFilters)) {
            $configuration['fields'][FiltersDef::GTE] = [
                'name' => FiltersDef::GTE,
                'type'        => $type,
                'description' => 'Greater than or equals',
            ];
        }

        if (in_array(FiltersDef::ISNULL, $allowedFilters)) {
            $configuration['fields'][FiltersDef::ISNULL] = [
                'name' => FiltersDef::ISNULL,
                'type'        => Type::boolean(),
                'description' => 'Takes a boolean.  If TRUE return results where the field is null. '
                    . 'If FALSE returns results where the field is not null. '
                    . 'Acts as "isEmpty" for collection filters.  A value of false will '
                    . 'be handled as though it were null.',
            ];
        }

        if (in_array(FiltersDef::BETWEEN, $allowedFilters)) {
            $configuration['fields'][FiltersDef::BETWEEN] = [
                'name' => FiltersDef::BETWEEN,
                'type' => new InputObjectType([
                    'name' => $fieldName . '_' . FiltersDef::BETWEEN . '_' . 'fields',
                    'fields' => [
                        'from' => [
                            'name' => $fieldName . '_' . FiltersDef::BETWEEN . '_from',
                            'type' => $type,
                            'description' => 'Low value of between',
                        ],
                        'to' => [
                            'name' => $fieldName . '_' . FiltersDef::BETWEEN . '_to',
                            'type' => $type,
                            'description' => 'High value of between',
                        ]
                    ]
                ]),
                'description' => 'Is between from and to inclusive of from and to.  Good substitute for DateTime Equals.',
            ];
        }

        if (in_array(FiltersDef::IN, $allowedFilters)) {
            $configuration['fields'][FiltersDef::IN] = [
                'name' => FiltersDef::IN,
                'type'        => Type::listOf($type),
                'description' => 'In the list of values as an array',
            ];
        }

        if (in_array(FiltersDef::NOTIN, $allowedFilters)) {
            $configuration['fields'][FiltersDef::NOTIN] = [
                'name' => FiltersDef::NOTIN,
                'type'        => Type::listOf($type),
                'description' => 'Not in the list of values as an array',
            ];
        }

        if (in_array(FiltersDef::STARTSWITH, $allowedFilters)
            && $type === Type::string() && $type === Type::id()) {

            $configuration['fields'][FiltersDef::STARTSWITH] = [
                'name' => FiltersDef::STARTSWITH,
                'type'        => $type,
                'description' => 'Starts with the value.  Strings only.',
            ];
        }

        if (in_array(FiltersDef::ENDSWITH, $allowedFilters)
            && $type === Type::string() && $type === Type::id()) {

            $configuration['fields'][FiltersDef::ENDSWITH] = [
                'name' => FiltersDef::ENDSWITH,
                'type'        => $type,
                'description' => 'End with the value.  Strings only.',
            ];
        }

        if (in_array(FiltersDef::CONTAINS, $allowedFilters)
            && $type === Type::string() && $type === Type::id()) {

            $configuration['fields'][FiltersDef::CONTAINS] = [
                'name' => FiltersDef::CONTAINS,
                'type'        => $type,
                'description' => 'Contains the value.  Strings only.',
            ];
        }

        return parent::__construct($configuration);
    }
}
