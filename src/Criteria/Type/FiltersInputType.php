<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Filters as FiltersDef;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class FiltersInputType extends InputObjectType
{
    /** @param string[] $allowedFilters */
    public function __construct(string $typeName, string $fieldName, Type $type, array $allowedFilters)
    {
        $fields       = [];
        $descriptions = FiltersDef::getDescriptions();

        foreach ($allowedFilters as $filter) {
            switch ($filter) {
                case FiltersDef::SORT:
                    $fields[$filter] = [
                        'name'        => $filter,
                        'type'        => Type::string(),
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::ISNULL:
                    $fields[$filter] = [
                        'name'        => $filter,
                        'type'        => Type::boolean(),
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::BETWEEN:
                    /** @psalm-suppress InvalidArgument */
                    $inputObjectType = new InputObjectType([
                        'name' => $typeName . '_' . $fieldName . '_filters_' . FiltersDef::BETWEEN . '_fields',
                        'fields' => [
                            'from' => [
                                'name'        => 'from',
                                'type'        => $type,
                                'description' => 'Low value of between',
                            ],
                            'to' => [
                                'name'        => 'to',
                                'type'        => $type,
                                'description' => 'High value of between',
                            ],
                        ],
                        'description' => 'Between `from` and `to',
                    ]);

                    $fields[$filter] = [
                        'name'        => $filter,
                        'type'        => $inputObjectType,
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::IN:
                case FiltersDef::NOTIN:
                    $fields[$filter] = [
                        'name'        => $filter,
                        'type'        => Type::listOf($type),
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::STARTSWITH:
                case FiltersDef::ENDSWITH:
                case FiltersDef::CONTAINS:
                    if ($type !== Type::string() && $type !== Type::id()) {
                        break;
                    }
                    // break intentionally omitted
                default:
                    $fields[$filter] = [
                        'name'        => $filter,
                        'type'        => $type,
                        'description' => $descriptions[$filter],
                    ];
            }
        }

        /** @psalm-suppress InvalidArgument */
        parent::__construct([
            'name' => $typeName . '_' . $fieldName . '_filters',
            'description' => 'Field filters',
            'fields' => static fn () => $fields,
        ]);
    }
}
