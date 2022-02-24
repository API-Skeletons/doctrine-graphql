<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Take no action on an association.  This class exists to
 * differentiate associations inside generated config.
 */
class AssociationDefault implements
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
