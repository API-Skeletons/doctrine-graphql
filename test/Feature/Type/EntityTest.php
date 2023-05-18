<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;

class EntityTest extends AbstractTest
{
    public function testEntityMetadata(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'entityTest']));

        $entity = $driver->get(TypeManager::class)->build(Entity::class, Recording::class);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals(Recording::class, $entity->getEntityClass());
        $this->assertEquals('Entity Test Recordings', $entity->getDescription());

        $metadata = $entity->getMetadata();

        $this->assertEquals(1, $metadata['byValue']);
        $this->assertEquals(null, $metadata['namingStrategy']);

        $this->assertEquals(
            ToInteger::class,
            $metadata['fields']['id']['strategy'],
        );
        $this->assertEquals(
            FieldDefault::class,
            $metadata['fields']['source']['strategy'],
        );
        $this->assertEquals(
            AssociationDefault::class,
            $metadata['fields']['performance']['strategy'],
        );
        $this->assertEquals(
            AssociationDefault::class,
            $metadata['fields']['users']['strategy'],
        );

        $this->assertEquals([], $metadata['filters']);

        $this->assertEquals('Entity Test Recordings', $metadata['description']);
        $this->assertEquals('Entity Test ID', $metadata['fields']['id']['description']);
        $this->assertEquals('Entity Test Source', $metadata['fields']['source']['description']);
        $this->assertEquals('Entity Test Performance', $metadata['fields']['performance']['description']);
        $this->assertEquals('Entity Test Users', $metadata['fields']['users']['description']);

        $this->assertEquals('entitytestrecording_entityTest', $metadata['typeName']);
    }
}
