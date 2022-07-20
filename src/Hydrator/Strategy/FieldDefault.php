<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Return the same value
 */
class FieldDefault extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract(mixed $value, ?object $object = null): mixed
    {
        return $value;
    }

    /**
     * @param mixed[]|null $data
     *
     * @codeCoverageIgnore
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        return $value;
    }
}
