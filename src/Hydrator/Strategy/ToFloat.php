<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Transform a number value into a php native float
 *
 * @returns float
 */
class ToFloat extends AbstractCollectionStrategy implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        return floatval($value);
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        if (is_null($value)) {
            return $value;
        }

        return floatval($value);
    }
}
