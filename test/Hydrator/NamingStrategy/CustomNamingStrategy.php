<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Hydrator\NamingStrategy;

use Laminas\Hydrator\NamingStrategy\NamingStrategyInterface;

class CustomNamingStrategy implements NamingStrategyInterface
{
    public function extract(string $name, ?object $object = null): string
    {
        return $name;
    }

    public function hydrate(string $name, ?array $data = null): string
    {
        return $name;
    }
}
