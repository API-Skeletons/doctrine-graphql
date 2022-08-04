<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

use Closure;
use GraphQL\Error\Error;
use Psr\Container\ContainerInterface;

use function strtolower;

abstract class AbstractContainer implements ContainerInterface
{
    /** @var mixed[] */
    protected array $register = [];

    public function has(string $id): bool
    {
        return isset($this->register[strtolower($id)]);
    }

    /**
     * @param $params
     * @throws Error
     */
    public function get(string $id, ...$params): mixed
    {
        $id = strtolower($id);

        if (! isset($this->register[$id])) {
            throw new Error($id . ' is not registered');
        }

        if ($this->register[$id] instanceof Closure) {
            $closure = $this->register[$id];

            $this->register[$id] = $closure($this, $params);
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
}
