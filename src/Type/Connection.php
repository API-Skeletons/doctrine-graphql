<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Buildable;
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
    public function __construct(AbstractContainer $container, string $typeName, array $params)
    {
        assert($params[0] instanceof ObjectType);
        $objectType = $params[0];

        $configuration = [
            'name' => $typeName,
            'description' => 'Connection for ' . $typeName,
            'fields' => [
                'edges' => Type::listOf($container
                    ->build(Node::class, $typeName . '_Node', $objectType)),
                'totalCount' => Type::nonNull(Type::int()),
                'pageInfo' => $container->get('PageInfo'),
            ],
        ];

        parent::__construct($configuration);
    }
}
