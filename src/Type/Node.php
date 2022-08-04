<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * This type is built within the TypeManager
 */
class Node extends ObjectType
{
    public function __construct(ObjectType $objectType, string $objectName)
    {
        $configuration = [
            'name' => $objectName . '_Node',
            'fields' => [
                'node' => $objectType,
                'cursor' => Type::nonNull(Type::string()),
            ],
        ];

        parent::__construct($configuration);
    }
}
