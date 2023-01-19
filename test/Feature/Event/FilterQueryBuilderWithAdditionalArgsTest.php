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
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use League\Event\EventDispatcher;

use function array_keys;
use function reset;

/**
 * Use the resolve argument of $args on the FilterQueryBuilder object to filter the query builder
 */
class FilterQueryBuilderWithAdditionalArgsTest extends AbstractTest
{
    public function testEvent(): void
    {
        $driver = new Driver($this->getEntityManager());
        $driver->get(EventDispatcher::class)->subscribeTo(
            'filter.querybuilder',
            function (FilterQueryBuilder $event): void {
                $event->getQueryBuilder()
                    ->andWhere($event->getQueryBuilder()->expr()->eq('entity.id', $event->getArgs()['id']));

                $this->assertEmpty($event->getObjectValue());
                $this->assertEquals('contextTest', $event->getContext());
                $this->assertIsArray($event->getArgs());
                $this->assertEquals(1, $event->getArgs()['id']);
                $this->assertEquals('dead', $event->getArgs()['filter']['name_contains']);
                $this->assertInstanceOf(ResolveInfo::class, $event->getInfo());
            },
        );

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artists' => [
                        'type' => $driver->connection($driver->type(Artist::class)),
                        'args' => [
                            'id' => Type::int(),
                            'filter' => $driver->filter(Artist::class),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query = '{
            artists (filter: { name_contains: "dead"} id: 1)
                { edges { node { id name performances { edges { node { venue recordings { edges { node { source } } } } } } } } }
        }';

        $result = GraphQL::executeQuery($schema, $query, null, 'contextTest');

        $data = $result->toArray()['data'];
        $this->assertEquals('Grateful Dead', $data['artists']['edges'][0]['node']['name']);
    }
}
