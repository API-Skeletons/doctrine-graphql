<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\Manager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

class DriverTest extends AbstractTest
{
    public function testCreateDriverWithoutConfig(): void
    {
        $driver = new Driver($this->getEntityManager());

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->getMetadata());
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(User::class));
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(Artist::class));
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(Performance::class));
        $this->assertInstanceOf(Entity::class, $driver->getMetadata()->getEntity(Recording::class));
    }

    public function testCreateDriverWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config = new Config([
            'group' => 'default',
            'useHydratorCache' => true,
            'limit' => 1000,
            'usePartials' => true,
        ]);

        $driver = new Driver($this->getEntityManager(), $config, null, $container);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->getMetadata());
    }

    public function testBuildGraphQLSchema(): void
    {
        $driver = new Driver($this->getEntityManager());

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = "{ artist { id name } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

//        Manager::show();

        print_r($output);

    }
}
