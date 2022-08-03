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
        $pageInfo = new PageInfo();

        $this
            ->set('tinyint', static fn () => Type::int())
            ->set('smallint', static fn () => Type::int())
            ->set('integer', static fn () => Type::int())
            ->set('int', static fn () => Type::int())
            ->set('boolean', static fn () => Type::boolean())
            ->set('decimal', static fn () => Type::float())
            ->set('float', static fn () => Type::float())
            ->set('bigint', static fn () => Type::string())
            ->set('string', static fn () => Type::string())
            ->set('text', static fn () => Type::string())
            ->set('array', static fn () => Type::listOf(Type::string()))
            ->set('datetime', static fn () => new DateTimeType())
            ->set('PageInfo', static fn () => new PageInfo())
        ;
    }
}
