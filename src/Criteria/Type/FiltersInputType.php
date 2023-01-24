<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Filters as FiltersDef;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function in_array;

class FiltersInputType extends InputObjectType
{
    /** @param string[] $allowedFilters */
    public function __construct(string $typeName, string $fieldName, Type $type, array $allowedFilters)
    {
        $descriptions = FiltersDef::getDescriptions();

        $configuration = [
            'name' => $typeName . '_' . $fieldName . '_filters',
            'description' => 'Field filters',
            'fields' => [],
        ];

        foreach (FiltersDef::toArray() as $filter) {
            if (! in_array($filter, $allowedFilters)) {
                continue;
            }

            switch ($filter) {
                case FiltersDef::SORT:
                    $configuration['fields'][$filter] = [
                        'name' => $filter,
                        'type' => Type::string(),
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::ISNULL:
                    $configuration['fields'][$filter] = [
                        'name' => $filter,
                        'type' => Type::boolean(),
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::BETWEEN:
                    /** @psalm-suppress InvalidArgument */
                    $inputObjectType = new InputObjectType([
                        'name' => $typeName . '_' . $fieldName . '_filters_' . FiltersDef::BETWEEN . '_fields',
                        'fields' => [
                            'from' => [
                                'name' => 'from',
                                'type' => $type,
                                'description' => 'Low value of between',
                            ],
                            'to' => [
                                'name' => 'to',
                                'type' => $type,
                                'description' => 'High value of between',
                            ],
                        ],
                        'description' => 'Between `from` and `to',
                    ]);

                    $configuration['fields'][FiltersDef::BETWEEN] = [
                        'name' => FiltersDef::BETWEEN,
                        'type' => $inputObjectType,
                        'description' => $descriptions[$filter],
                    ];
                    break;

                case FiltersDef::IN:
                case FiltersDef::NOTIN:
                    $configuration['fields'][$filter] = [
                        'name' => $filter,
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
                    $configuration['fields'][$filter] = [
                        'name' => $filter,
                        'type' => $type,
                        'description' => $descriptions[$filter],
                    ];
            }
        }

        /** @psalm-suppress InvalidArgument */
        parent::__construct($configuration);
    }
}
