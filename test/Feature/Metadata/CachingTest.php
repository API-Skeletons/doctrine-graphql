<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\Error\Error;

class CachingTest extends AbstractTest
{
    public function testCacheMetadata(): void
    {
        $driver = new Driver($this->getEntityManager());

        $metadataConfig = $driver->get('metadataConfig');

        unset($driver);

        $driver = new Driver($this->getEntityManager(), null, $metadataConfig);
        $this->assertInstanceOf(Entity::class, $driver->get(TypeManager::class)->build(Entity::class, User::class));
    }

    public function testStaticMetadata(): void
    {
        $metadataConfig = [
            'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User' => [
                'entityClass' => 'ApiSkeletonsTest\Doctrine\GraphQL\Entity\User',
                'documentation' => '',
                'byValue' => 1,
                'namingStrategy' => null,
                'fields' => [
                    'name' => [
                        'strategy' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault',
                        'documentation' => '',
                    ],
                    'recordings' => [
                        'strategy' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault',
                        'excludeCriteria' => ['eq'],
                        'documentation' => '',
                    ],
                ],

                'strategies' => [
                    'name' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault',
                    'email' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault',
                    'id' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger',
                    'recordings' => 'ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault',
                ],
                'filters' => [],
                'typeName' => 'User',
            ],
        ];

        $driver = new Driver($this->getEntityManager(), null, $metadataConfig);
        $this->assertInstanceOf(Entity::class, $driver->get(TypeManager::class)->build(Entity::class, User::class));

        $this->expectException(Error::class);
        $this->assertInstanceOf(Entity::class, $driver->get(TypeManager::class)->build(Entity::class, Artist::class));
    }
}
