<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;

use function intval;

/**
 * Transform a number value into a php native integer
 *
 * @returns integer
 */
class ToInteger extends AbstractCollectionStrategy implements
    StrategyInterface,
    Invokable
{
    public function extract(mixed $value, ?object $object = null): mixed
    {
        if ($value === null) {
            return $value;
        }

        return intval($value);
    }

    /**
     * @param mixed[]|null $data
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        if ($value === null) {
            return $value;
        }

        return intval($value);
    }
}
