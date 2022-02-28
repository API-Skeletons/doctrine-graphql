<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Laminas\Hydrator\Filter\FilterInterface;

class Password implements
    FilterInterface,
    Invokable
{
    public function filter(string $property, ?object $instance = null): bool
    {
        $excludeFields = [
            'password',
            'secret',
        ];

        return (! in_array($property, $excludeFields));
    }
}
