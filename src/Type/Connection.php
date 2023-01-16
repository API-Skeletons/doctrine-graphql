<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

use function assert;

/**
 * This type is built within the TypeManager
 */
class Connection extends ObjectType implements
    Buildable
{
    /** @param mixed[] $params */
    public function __construct(TypeManager $typeManager, string $typeName, array $params)
    {
        assert($params[0] instanceof ObjectType);
        $objectType = $params[0];

        $configuration = [
            'name' => $typeName,
            'description' => 'Connection for ' . $typeName,
            'fields' => [
                'edges' => Type::listOf($typeManager
                    ->build(Node::class, $typeName . '_Node', $objectType)),
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $typeManager->get('PageInfo'),
            ],
        ];

        parent::__construct($configuration);
    }
}
