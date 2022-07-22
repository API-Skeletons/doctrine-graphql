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
                'edges' => Type::listOf($this->getNodeObjectType($objectType)),
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $this->getPageInfo($objectType->name),
            ],
        ];

        return new ObjectType($configuration);
    }

    private function getNodeObjectType(ObjectType $objectType): ObjectType
    {
        $configuration = [
            'name' => $objectType->name . '_Node',
            'fields' => [
                'node' => $objectType,
                'cursor' => Type::nonNull(Type::string()),
            ],
        ];

        return new ObjectType($configuration);
    }

    private function getPageInfo(string $typeName): ObjectType
    {
        $configuration = [
            'name' => $typeName . '_PageInfo',
            'description' => 'Page information',
            'fields' => [
                'endCursor' => Type::nonNull(Type::string()),
                'hasNextPage' => Type::nonNull(Type::boolean()),
            ],
        ];

        return new ObjectType($configuration);
    }
}
