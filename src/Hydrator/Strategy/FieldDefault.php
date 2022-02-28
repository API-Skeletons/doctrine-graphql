<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Laminas\Hydrator\Strategy\StrategyInterface;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;

/**
 * Return the same value
 */
class FieldDefault extends AbstractCollectionStrategy implements
    StrategyInterface,
    Invokable
{
    public function extract($value, ?object $object = null)
    {
        return $value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value, ?array $data)
    {
        return $value;
    }
}
