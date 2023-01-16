<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Transform a value into a php native boolean
 *
 * @returns float
 */
class ToBoolean extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract(mixed $value, object|null $object = null): bool|null
    {
        if ($value === null) {
            // @codeCoverageIgnoreStart
            return $value;
            // @codeCoverageIgnoreEnd
        }

        return (bool) $value;
    }

    /**
     * @param mixed[]|null $data
     *
     * @codeCoverageIgnore
     */
    public function hydrate(mixed $value, array|null $data): bool|null
    {
        if ($value === null) {
            return $value;
        }

        return (bool) $value;
    }
}
