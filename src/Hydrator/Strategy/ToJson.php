<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Transform a value to JSON
 *
 * @returns string
 */
class ToJson implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        if (is_null($value)) {
            return $value;
        }

        return json_decode($value);
    }
}
