<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionException;

use function assert;

class TypeManager extends AbstractContainer
{
    public function __construct()
    {
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
            ->set('simple_array', static fn () => Type::listOf(Type::string()))
            ->set('guid', static fn () => Type::string())
            ->set('json', static fn () => new Json())
            ->set('date', static fn () => new Date())
            ->set('datetime', static fn () => new DateTime())
            ->set('datetimetz', static fn () => new DateTimeTZ())
            ->set('time', static fn () => new Time())
            ->set('date_immutable', static fn () => new DateImmutable())
            ->set('datetime_immutable', static fn () => new DateTimeImmutable())
            ->set('datetimetz_immutable', static fn () => new DateTimeTZImmutable())
            ->set('time_immutable', static fn () => new TimeImmutable())
            ->set('PageInfo', static fn () => new PageInfo());
    }

    /**
     * @param mixed[] $params
     *
     * @throws Error
     * @throws ReflectionException
     */
    public function build(string $typeClassName, string $typeName, mixed ...$params): Type
    {
        if ($this->has($typeName)) {
            return $this->get($typeName);
        }

        assert((new ReflectionClass($typeClassName))->implementsInterface(Buildable::class));

        return $this
            ->set($typeName, new $typeClassName($this, $typeName, $params))
            ->get($typeName);
    }
}
