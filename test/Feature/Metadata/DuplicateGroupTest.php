<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use AssertionError;

class DuplicateGroupTest extends AbstractTest
{
    public function testDuplicateEntityAttributeForGroup(): void
    {
        $this->expectException(AssertionError::class);

        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'DuplicateGroup']));

        $metadataConfig = $driver->get(Metadata::class)->getMetadataConfig();
    }

    public function testDuplicateEntityAttributeForField(): void
    {
        $this->expectException(AssertionError::class);

        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'DuplicateGroupField']));

        $metadataConfig = $driver->get(Metadata::class)->getMetadataConfig();
    }

    public function testDuplicateEntityAttributeForAssociation(): void
    {
        $this->expectException(AssertionError::class);

        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'DuplicateGroupAssociation']));

        $metadataConfig = $driver->get(Metadata::class)->getMetadataConfig();
    }
}
