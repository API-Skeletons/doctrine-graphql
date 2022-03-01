<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
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

    public function testEq(): void
    {
        $query = '{ artist { performances ( filter: {id: 2} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(2, $data['artist'][0]['performances'][0]['id']);
    }

    public function testNeq(): void
    {
        $query = '{ artist { performances ( filter: {id_neq: 2} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist'][0]['performances']));
        $this->assertEquals(1, $data['artist'][0]['performances'][0]['id']);
    }

    public function testlt(): void
    {
        $query = '{ artist { performances ( filter: {id_lt: 2} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(1, $data['artist'][0]['performances'][0]['id']);
    }

    public function testlte(): void
    {
        $query = '{ artist { performances ( filter: {id_lte: 2} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist'][0]['performances']));
        $this->assertEquals(1, $data['artist'][0]['performances'][0]['id']);
    }

    public function testgt(): void
    {
        $query = '{ artist { performances ( filter: {id_gt: 1} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist'][0]['performances']));
        $this->assertEquals(2, $data['artist'][0]['performances'][0]['id']);
    }

    public function testgte(): void
    {
        $query = '{ artist { performances ( filter: {id_gte: 2} ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist'][0]['performances']));
        $this->assertEquals(2, $data['artist'][0]['performances'][0]['id']);
    }

    public function testbetween(): void
    {
        $query = '{ artist { performances ( filter: {id_between: { from: 2, to: 3 } } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist'][0]['performances']));
        $this->assertEquals(2, $data['artist'][0]['performances'][0]['id']);
    }

    public function testcontains(): void
    {
        $query = '{ artist { performances ( filter: { venue_contains: "ill" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(2, $data['artist'][0]['performances'][0]['id']);
    }

    public function teststartwith(): void
    {
        $query = '{ artist { performances ( filter: { venue_startswith: "Soldier" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(4, $data['artist'][0]['performances'][0]['id']);
    }

    public function testendswith(): void
    {
        $query = '{ artist { performances ( filter: { venue_endswith: "University" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist'][0]['performances']));
        $this->assertEquals(3, $data['artist'][0]['performances'][0]['id']);
    }

    public function testin(): void
    {
        $query = '{ artist { performances ( filter: { id_in: [1,2,3] } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist'][0]['performances']));
        $this->assertEquals(1, $data['artist'][0]['performances'][0]['id']);
    }

    public function testnotin(): void
    {
        $query = '{ artist { performances ( filter: { id_notin: [3,4] } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist'][0]['performances']));
        $this->assertEquals(1, $data['artist'][0]['performances'][0]['id']);
    }

    public function testsort(): void
    {
        $query = '{ artist { performances ( filter: { id_sort: "desc" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist'][0]['performances']));
        $this->assertEquals(4, $data['artist'][0]['performances'][0]['id']);


        $query = '{ artist { performances ( filter: { venue_sort: "asc" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist'][0]['performances']));
        $this->assertEquals(3, $data['artist'][0]['performances'][0]['id']);


        $query = '{ artist { performances ( filter: { venue_sort: "desc" } ) { id } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist'][0]['performances']));
        $this->assertEquals(4, $data['artist'][0]['performances'][0]['id']);
    }
}
