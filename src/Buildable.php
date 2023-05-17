<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

/**
 * Types that can be built must implement this interface
 */
interface Buildable
{
    /** @param mixed[] $params */
    public function __construct(AbstractContainer $container, string $typeName, array $params);
}
