<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

class Metadata
{
    public function __construct(
        protected array|null $metadataConfig,
    ) {
    }

    public function __invoke(): array|null
    {
        return $this->metadataConfig;
    }
}
