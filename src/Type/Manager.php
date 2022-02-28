<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Error\Error;

use function array_keys;
use function print_r;
use function strtolower;

class Manager
{
    /** @var mixed[] */
    protected static array $registeredTypes = [];

    public static function has(string $name): bool
    {
        $name = strtolower($name);

        return isset(self::$registeredTypes[$name]);
    }

    public static function get(string $name): mixed
    {
        $name = strtolower($name);

        if (! isset(self::$registeredTypes[$name])) {
            throw new Error($name . ' is not registered in the type manager');
        }

        return self::$registeredTypes[$name];
    }

    public static function set(string $name, mixed $value): void
    {
        $name = strtolower($name);

        if (isset(self::$registeredTypes[$name])) {
            throw new Error($name . ' is already registered in the type manager');
        }

        self::$registeredTypes[$name] = $value;
    }

    public static function show(): void
    {
        print_r(array_keys(self::$registeredTypes));
    }
}
