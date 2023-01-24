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

class TypeNameTest extends AbstractTest
{
    public function testEmptyGroupNameGlobalEnable(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'groupSuffix' => '',
            'entityPrefix' => 'ApiSkeletonsTest\\Doctrine\\GraphQL\\Entity\\',
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

        $query  = '{ artist { edges { node { performances ( filter: {venue: { neq: "test"} } ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertEquals('Artist', $driver->type(Artist::class)->name);
    }

    public function testEmptyGroupName(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'groupSuffix' => '',
            'group' => 'TypeNameTest',
            'entityPrefix' => 'ApiSkeletonsTest\\Doctrine\\GraphQL\\Entity\\',
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

        $query  = '{ artist { edges { node { performances ( filter: {venue: {neq: "test"} } ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertEquals('Artist', $driver->type(Artist::class)->name);
    }

    public function testEntityPrefix(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'entityPrefix' => 'ApiSkeletonsTest\\Doctrine\\GraphQL\\Entity\\',
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

        $query  = '{ artist { edges { node { performances ( filter: {venue: { neq: "test"} } ) { edges { node { venue } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $this->assertEquals('Artist_default', $driver->type(Artist::class)->name);
    }
}
