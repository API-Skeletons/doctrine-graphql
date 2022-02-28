<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\Error\Error;

class CachingTest extends AbstractTest
{
    public function testCacheMetadata(): void
    {
        $driver = new Driver($this->getEntityManager());

        $metadataConfig = $driver->getMetadata()->getMetadataConfig();
        unset($driver);

        $driver = new Driver($this->getEntityManager(), null, $metadataConfig);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->get(User::class));
    }

    public function testStaticMetadata(): void
    {
        $metadataConfig = [
            'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User' => [
                'entityClass' => 'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User',
                'byValue' => 1,
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

        $driver = new Driver($this->getEntityManager(), null, $metadataConfig);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->get(User::class));

        $this->expectException(Error::class);
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->get(Artist::class));
    }
}
