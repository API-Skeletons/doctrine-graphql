<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

/**
 * Types that can be built with the TypeManager must implement this interface
 */
interface Buildable
{
    /** @param mixed[] $params */
    public function __construct(TypeManager $typeManager, string $typeName, array $params);
}
