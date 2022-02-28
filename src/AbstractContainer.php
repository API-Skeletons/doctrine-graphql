<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

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
     * @throws Error
     */
    public function get(string $id): mixed
    {
        $id = strtolower($id);

        if (! isset($this->register[$id])) {
            throw new Error($id . ' is not registered');
        }

        return $this->register[$id];
    }

    public function set(string $id, mixed $value): self
    {
        $id = strtolower($id);

        if ($this->has($id)) {
            throw new Error($id . ' is already registered');
        }

        $this->register[$id] = $value;

        return $this;
    }
}
