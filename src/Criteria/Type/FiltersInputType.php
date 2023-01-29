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
            $filterType = $type;

            switch ($filter) {
                case FiltersDef::SORT:
                    $filterType = Type::string();
                    break;
                case FiltersDef::ISNULL:
                    $filterType = Type::boolean();
                    break;
                case FiltersDef::BETWEEN:
                    $filterType = $this->buildBetweenInputObject(
                        $typeName,
                        $fieldName,
                        $type,
                    );
                    break;
                case FiltersDef::IN:
                case FiltersDef::NOTIN:
                    $filterType = Type::listOf($type);
                    break;
                case FiltersDef::STARTSWITH:
                case FiltersDef::ENDSWITH:
                case FiltersDef::CONTAINS:
                    if ($type !== Type::string() && $type !== Type::id()) {
                        continue 2;
                    }

                    break;
            }

            $fields[$filter] = [
                'name'        => $filter,
                'type'        => $filterType,
                'description' => $descriptions[$filter],
            ];
        }

        /** @psalm-suppress InvalidArgument */
        parent::__construct([
            'name' => $typeName . '_' . $fieldName . '_filters',
            'description' => 'Field filters',
            'fields' => static fn () => $fields,
        ]);
    }

    private function buildBetweenInputObject(
        string $typeName,
        string $fieldName,
        Type $type,
    ): InputObjectType {
        /** @psalm-suppress InvalidArgument */
        return new InputObjectType([
            'name' => $typeName
                . '_' . $fieldName
                . '_filters_'
                . FiltersDef::BETWEEN
                . '_fields',
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
            'description' => 'Between `from` and `to`',
        ]);
    }
}
