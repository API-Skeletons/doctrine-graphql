<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityDefinition;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use ArrayObject;
use League\Event\EventDispatcher;

use function array_keys;
use function sort;

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

    public function testSortFields(): void
    {
        $unsortedFields           = new ArrayObject();
        $unsortedFields['fields'] = [];

        $config = new Config(['sortFields' => true]);

        $driver = new Driver($this->getEntityManager(), $config);

        // Fields are only sorted after this event is fired
        $driver->get(EventDispatcher::class)->subscribeTo(
            User::class . '.definition',
            static function (EntityDefinition $event) use ($unsortedFields): void {
                $fields = $event->getDefinition()['fields']();

                $unsortedFields['fields'] = array_keys($fields);
            },
        );

        $graphQLType  = $driver->get(TypeManager::class)
            ->build(Entity::class, User::class)();
        $fields       = array_keys($graphQLType->getFields());
        $fieldsSorted = $fields;
        sort($fieldsSorted);

        $this->assertNotEquals(array_keys($unsortedFields['fields']), $fields);
        $this->assertEquals($fields, $fieldsSorted);
    }
}
