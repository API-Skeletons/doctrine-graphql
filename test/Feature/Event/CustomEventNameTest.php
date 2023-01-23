<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Event;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use Doctrine\ORM\QueryBuilder;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use League\Event\EventDispatcher;

use function array_keys;
use function reset;

class CustomEventNameTest extends AbstractTest
{
    public function testEvent(): void
    {
        $driver = new Driver($this->getEntityManager());
        $driver->get(EventDispatcher::class)->subscribeTo(
            'custom.test',
            function (FilterQueryBuilder $event): void {
                $this->assertInstanceOf(QueryBuilder::class, $event->getQueryBuilder());

                $entityAliasMap     = $event->getEntityAliasMap();
                $entityAliasMapKeys = array_keys($event->getEntityAliasMap());

                $this->assertEquals(Artist::class, reset($entityAliasMap));
                $this->assertEquals('entity', reset($entityAliasMapKeys));
            },
        );

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => $driver->connection($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class, 'custom.test'),
                    ],
                ],
            ]),
        ]);

        $query = '{
            artist (filter: { name: { contains: "dead" } } )
                { edges { node { id name performances { edges { node { venue recordings { edges { node { source } } } } } } } } }
        }';

        GraphQL::executeQuery($schema, $query);
    }
}
