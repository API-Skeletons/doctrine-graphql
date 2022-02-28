<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Take no action on an association.  This class exists to
 * differentiate associations inside generated config.
 */
class AssociationDefault extends AbstractCollectionStrategy implements
    StrategyInterface,
    Invokable
{
    public function extract(mixed $value, ?object $object = null): mixed
    {
        return $value;
    }

    /**
     * @param mixed[] |null $data
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        return $value;
    }
}
