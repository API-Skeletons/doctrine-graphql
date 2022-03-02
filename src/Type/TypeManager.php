<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Type\DateTime as DateTimeType;
use GraphQL\Type\Definition\Type;

class TypeManager extends AbstractContainer
{
    public function __construct()
    {
        $this
            ->set('tinyint', fn() => Type::int())
            ->set('smallint', fn() => Type::int())
            ->set('integer', fn() => Type::int())
            ->set('int', fn() => Type::int())
            ->set('boolean', fn() => Type::boolean())
            ->set('decimal', fn() => Type::float())
            ->set('float', fn() => Type::float())
            ->set('bigint', fn() => Type::string())
            ->set('string', fn() => Type::string())
            ->set('text', fn() => Type::string())
            ->set('array', fn() => Type::listOf(Type::string()))
            ->set('datetime', fn() => new DateTimeType());
    }
}
