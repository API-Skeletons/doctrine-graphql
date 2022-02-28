<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Strategy\StrategyInterface;

use function json_decode;
use function json_encode;

use const JSON_UNESCAPED_SLASHES;

/**
 * Transform a value to JSON
 *
 * @returns string
 */
class ToJson extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract(mixed $value, ?object $object = null): mixed
    {
        if ($value === null) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param mixed[]|null $data
     */
    public function hydrate(mixed $value, ?array $data): mixed
    {
        if ($value === null) {
            return $value;
        }

        return json_decode($value);
    }
}
