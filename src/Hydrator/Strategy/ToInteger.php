<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

use function intval;

/**
 * Transform a number value into a php native integer
 *
 * @returns integer
 */
class ToInteger extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract(mixed $value, ?object $object = null): mixed
    {
        if ($value === null) {
            // @codeCoverageIgnoreStart
            return $value;
            // @codeCoverageIgnoreEnd
        }

        return intval($value);
    }

    /**
     * @param mixed[]|null $data
     *
     * @codeCoverageIgnore
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        if ($value === null) {
            return $value;
        }

        return intval($value);
    }
}
