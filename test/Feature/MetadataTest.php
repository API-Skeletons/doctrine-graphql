<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Exception\UnmappedEntityMetadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use Psr\Container\ContainerInterface;

class MetadataTest extends AbstractTest
{
    public function testNonDefaultGroup(): void
    {
        $config = new Config([
            'group' => 'test1',
            'useHydratorCache' => true,
            'limit' => 1000,
            'usePartials' => true,
        ]);

        $driver = new Driver($this->getEntityManager(), $config);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(User::class));

        $this->expectException(UnmappedEntityMetadata::class);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(Artist::class));
    }

    public function testCreateDriverWithStaticMetadata(): void
    {
        $config = new Config([
            'group' => 'default',
            'useHydratorCache' => true,
            'limit' => 1000,
            'usePartials' => true,
        ]);

        $metadataConfig = [
            'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User' => [
                'entityClass' => 'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User',
                'byValue' => 1,
                'hydrator' => 'default',
                'namingStrategy' => null,
                'strategies' => [
                    'name' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault',
                    'email' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault',
                    'id' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger',
                    'recordings' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault',
                ],
                'filters' => [],
                'documentation' => [
                    '_entity' => 'User',
                    'name' => 'User name',
                    'email' => 'User email',
                    'id' => 'Primary key',
                    'recordings' => 'Recordings',
                ],
                'typeName' => 'User',
            ]
        ];

        $driver = new Driver($this->getEntityManager(), $config, $metadataConfig);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(User::class));

        $this->expectException(UnmappedEntityMetadata::class);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(Artist::class));
    }
}