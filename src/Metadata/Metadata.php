<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;

class Metadata
{
    public function __construct(
        protected AbstractContainer $container,
        protected array|null $metadataConfig,
    ) {
    }

    public function getMetadataConfig(): array|null
    {
        return $this->metadataConfig;
    }
}
