<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

class DriverTest extends AbstractTest
{
    public function testGetInvalidService(): void
    {
        $driver = new Driver($this->getEntityManager());

        $this->expectException(Error::class);
        $driver->get('invalid');
    }

    public function testCreateDriverWithoutConfig(): void
    {
        $driver = new Driver($this->getEntityManager());

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->get(Metadata::class));
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(User::class));
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(Artist::class));
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(Performance::class));
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(Recording::class));
    }

    public function testCreateDriverWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = new Config([
            'group' => 'default',
            'useHydratorCache' => true,
            'limit' => 1000,
        ]);

        $driver = new Driver($this->getEntityManager(), $config, null, $container);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->get(Metadata::class));
    }

    public function testNonDefaultGroup(): void
    {
        $config = new Config([
            'group' => 'testNonDefaultGroup',
            'useHydratorCache' => true,
            'limit' => 1000,
        ]);

        $driver = new Driver($this->getEntityManager(), $config);
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(User::class));

        $this->expectException(Error::class);
        $this->assertInstanceOf(Entity::class, $driver->get(Metadata::class)->get(Artist::class));
    }

    /**
     * This tests much of the whole system.  Each part is tested in detail
     * elsewhere.
     */
    public function testBuildGraphQLSchema(): void
    {
        $driver = new Driver($this->getEntityManager());

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => $driver->connection($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = '{
            artist (filter: { name_contains: "dead" })
                { edges { node { id name performances { edges { node { venue recordings { edges { node { source } } } } } } } } }
        }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals('Grateful Dead', $output['data']['artist']['edges'][0]['node']['name']);
    }

    public function testUseHydratorCache(): void
    {
        $config = new Config(['useHydratorCache' => true]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => $driver->connection($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = '{
            artist (filter: { name_contains: "dead" })
                { edges { node { id name performances { edges { node { venue recordings { edges { node { source } } } } } } } } }
        }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals('Grateful Dead', $output['data']['artist']['edges'][0]['node']['name']);
    }
}
