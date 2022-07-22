<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Collection
{
    public function get(ObjectType $objectType): ObjectType
    {
        $configuration = [
            'name' => $objectType->name . '_Connection',
            'description' => 'Connection for ' . $objectType->name,
            'fields' => [
                'collection' => Type::listOf($objectType),
                'pagination' => Type::nonNull($this->getPaginationInfo($objectType->name)),
            ],
        ];

        return new ObjectType($configuration);
    }

    private function getPaginationInfo(string $typeName)
    {
        $configuration = [
            'name' => $typeName . '_PaginationInfo',
            'description' => 'Pagination information',
            'fields' => [
                'page' => Type::nonNull(Type::int()),
                'pageCount' => Type::nonNull(Type::int()),
                'pageSize' => Type::nonNull(Type::int()),
                'totalItems' => Type::nonNull(Type::int()),
            ],
        ];

        return new ObjectType($configuration);
    }
}
