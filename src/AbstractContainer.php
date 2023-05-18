<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use Closure;
use GraphQL\Error\Error;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

use function assert;
use function strtolower;

abstract class AbstractContainer implements ContainerInterface
{
    /** @var mixed[] */
    protected array $register = [];

    public function has(string $id): bool
    {
        return isset($this->register[strtolower($id)]);
    }

    /** @throws Error */
    public function get(string $id): mixed
    {
        $id = strtolower($id);

        if (! $this->has($id)) {
            throw new Error($id . ' is not registered');
        }

        if ($this->register[$id] instanceof Closure) {
            $closure = $this->register[$id];

            $this->register[$id] = $closure($this);
        }

        return $this->register[$id];
    }

    /**
     * This allows for a duplicate id to overwrite an existing registration
     */
    public function set(string $id, mixed $value): self
    {
        $id = strtolower($id);

        $this->register[$id] = $value;

        return $this;
    }

    /**
     * This function allows for buildable types.  The Type\Connection type is created this way
     * because it relies on the entity object type.  To create a custom buildable object type
     * it must implement the Buildable interface.
     *
     * @param mixed[] $params
     *
     * @throws Error
     * @throws ReflectionException
     */
    public function build(string $typeClassName, string $typeName, mixed ...$params): mixed
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
