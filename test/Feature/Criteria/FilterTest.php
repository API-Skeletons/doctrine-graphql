<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class FilterTest extends AbstractTest
{
    protected Schema $schema;

    public function setUp(): void
    {
        parent::setUp();

        $driver = new Driver($this->getEntityManager());

        $this->schema = new Schema([
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
    }

    public function testEquals(): void
    {
        $query = '{ artist { performances ( filter: {id: 2} ) { venue } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(2, count($data['artist'][0]['performances'][0]['id']));
    }
}
