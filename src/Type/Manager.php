<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Error\Error;

use function strtolower;

class Manager
{
    /** @var mixed[] */
    protected array $registeredTypes = [];

    public function has(string $name): bool
    {
        $name = strtolower($name);

        return isset($this->registeredTypes[$name]);
    }

    public function get(string $name): mixed
    {
        $name = strtolower($name);

        if (! isset($this->registeredTypes[$name])) {
            throw new Error($name . ' is not registered in the type manager');
        }

        return $this->registeredTypes[$name];
    }

    public function set(string $name, mixed $value): void
    {
        $name = strtolower($name);

        if (isset($this->registeredTypes[$name])) {
            throw new Error($name . ' is already registered in the type manager');
        }

        $this->registeredTypes[$name] = $value;
    }
}
