<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;

class EntityTest extends AbstractTest
{
    public function testEntityMetadata(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'entityTest']));

        $entity = $driver->get(Metadata::class)->get(Recording::class);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals(Recording::class, $entity->getEntityClass());
        $this->assertEquals('Entity Test Recordings', $entity->getDescription());

        $metadataConfig = $entity->getMetadataConfig();

        $this->assertEquals(1, $metadataConfig['byValue']);
        $this->assertEquals(null, $metadataConfig['namingStrategy']);

        $this->assertEquals(
            ToInteger::class,
            $metadataConfig['fields']['id']['strategy']
        );
        $this->assertEquals(
            FieldDefault::class,
            $metadataConfig['fields']['source']['strategy']
        );
        $this->assertEquals(
            AssociationDefault::class,
            $metadataConfig['fields']['performance']['strategy']
        );
        $this->assertEquals(
            AssociationDefault::class,
            $metadataConfig['fields']['users']['strategy']
        );

        $this->assertEquals([], $metadataConfig['filters']);

        $this->assertEquals('Entity Test Recordings', $metadataConfig['description']);
        $this->assertEquals('Entity Test ID', $metadataConfig['fields']['id']['description']);
        $this->assertEquals('Entity Test Source', $metadataConfig['fields']['source']['description']);
        $this->assertEquals('Entity Test Performance', $metadataConfig['fields']['performance']['description']);
        $this->assertEquals('Entity Test Users', $metadataConfig['fields']['users']['description']);

        $this->assertEquals('entitytestrecording_entityTest', $metadataConfig['typeName']);
    }
}
