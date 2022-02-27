<?php

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Error\Error;

class Manager
{
    static protected $registeredTypes = [];

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

    public static function show()
    {
        print_r(array_keys(self::$registeredTypes));
    }
}
