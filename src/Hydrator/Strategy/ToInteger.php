<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Transform a number value into a php native integer
 *
 * @returns integer
 */
class ToInteger implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        return intval($value);
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        if (is_null($value)) {
            return $value;
        }

        return intval($value);
    }
}
