<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Connection
{
    public function __construct(
        private TypeManager $typeManager
    ) {
    }

    public function get(ObjectType $objectType, ?string $objectName = null): ObjectType
    {
        $objectName ??= $objectType->name;

        $configuration = [
            'name' => $objectName . '_Connection',
            'description' => 'Connection for ' . $objectName,
            'fields' => [
                'edges' => Type::listOf($this->getNodeObjectType($objectType, $objectName)),
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $this->typeManager->get('PageInfo'),
            ],
        ];

        return new ObjectType($configuration);
    }

    private function getNodeObjectType(ObjectType $objectType, string $objectName): ObjectType
    {
        $configuration = [
            'name' => $objectName . '_Node',
            'fields' => [
                'node' => $objectType,
                'cursor' => Type::nonNull(Type::string()),
            ],
        ];

        return new ObjectType($configuration);
    }
}
