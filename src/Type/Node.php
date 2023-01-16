<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

use function assert;

/**
 * This type is built within the TypeManager
 */
class Node extends ObjectType implements
    Buildable
{
    /** @param mixed[] $params */
    public function __construct(TypeManager $typeManager, string $typeName, array $params)
    {
        assert($params[0] instanceof ObjectType);

        $configuration = [
            'name' => $typeName,
            'fields' => [
                'node' => $params[0],
                'cursor' => Type::nonNull(Type::string()),
            ],
        ];

        parent::__construct($configuration);
    }
}
