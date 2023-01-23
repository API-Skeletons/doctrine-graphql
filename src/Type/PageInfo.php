<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PageInfo extends ObjectType
{
    public function __construct()
    {
        $configuration = [
            'name' => uniqid(),
            'description' => 'Page information',
            'fields' => [
                'startCursor' => Type::nonNull(Type::string()),
                'endCursor' => Type::nonNull(Type::string()),
                'hasPreviousPage' => Type::nonNull(Type::boolean()),
                'hasNextPage' => Type::nonNull(Type::boolean()),
            ],
        ];

        parent::__construct($configuration);
    }
}
