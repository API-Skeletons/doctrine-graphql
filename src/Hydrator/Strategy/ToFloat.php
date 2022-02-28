<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use ApiSkeletons\Doctrine\GraphQL\Invokable;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;

use function floatval;

/**
 * Transform a number value into a php native float
 *
 * @returns float
 */
class ToFloat extends AbstractCollectionStrategy implements
    StrategyInterface,
    Invokable
{
    /**
     * @param mixed|null $object
     */
    public function extract(mixed $value, ?object $object = null): mixed
    {
        if ($value === null) {
            return $value;
        }

        return floatval($value);
    }

    /**
     * @param mixed[]|null $data
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        if ($value === null) {
            return $value;
        }

        return floatval($value);
    }
}
