<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use Psr\Container\ContainerInterface;

class EntityTest extends AbstractTest
{
    public function testEntityMetadata(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'entityTest']));

        $entity = $driver->getMetadata()->getEntity(Recording::class);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals(Recording::class, $entity->getEntityClass());
        $this->assertEquals('Entity Test Recordings', $entity->getDocs());

        $metadataConfig = $entity->getMetadataConfig();

        $this->assertEquals(1, $metadataConfig['byValue']);
        $this->assertEquals('default', $metadataConfig['hydrator']);
        $this->assertEquals(null, $metadataConfig['namingStrategy']);

        $this->assertEquals(ToInteger::class, $metadataConfig['strategies']['id']);
        $this->assertEquals(FieldDefault::class, $metadataConfig['strategies']['source']);
        $this->assertEquals(AssociationDefault::class, $metadataConfig['strategies']['performance']);
        $this->assertEquals(AssociationDefault::class, $metadataConfig['strategies']['users']);

        $this->assertEquals([], $metadataConfig['filters']);

        $this->assertEquals('Entity Test Recordings', $metadataConfig['documentation']['_entity']);
        $this->assertEquals('Entity Test ID', $metadataConfig['documentation']['id']);
        $this->assertEquals('Entity Test Source', $metadataConfig['documentation']['source']);
        $this->assertEquals('Entity Test Performance', $metadataConfig['documentation']['performance']);
        $this->assertEquals('Entity Test Users', $metadataConfig['documentation']['users']);

        $this->assertEquals('EntityTestRecording', $metadataConfig['typeName']);
    }
}
