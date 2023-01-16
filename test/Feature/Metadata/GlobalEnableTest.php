<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

class GlobalEnableTest extends AbstractTest
{
    public function testGlobalEnable(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'group' => 'globalEnable',
            'globalEnable' => true,
        ]));

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

        $query  = '{ artist { edges { node { performances ( filter: {venue_neq: "test"} ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertTrue($driver->get(Config::class)->getGlobalEnable());
    }

    public function testGlobalIgnoreFieldName(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'group' => 'globalEnable',
            'globalEnable' => true,
            'globalIgnore' => ['name'],
        ]));

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

        $query  = '{ artist { edges { node { name performances ( filter: {venue_neq: "test"} ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertEquals(
            'Cannot query field "name" on type "ApiSkeletonsTest_Doctrine_GraphQL_Entity_Artist_globalEnable".',
            $result->toArray()['errors'][0]['message'],
        );
    }

    public function testGlobalIgnoreAssociationName(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'group' => 'globalEnable',
            'globalEnable' => true,
            'globalIgnore' => ['performances'],
        ]));

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

        $query  = '{ artist { edges { node { name performances ( filter: {venue_neq: "test"} ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertEquals(
            'Cannot query field "performances" on type "ApiSkeletonsTest_Doctrine_GraphQL_Entity_Artist_globalEnable".',
            $result->toArray()['errors'][0]['message'],
        );
    }
}
