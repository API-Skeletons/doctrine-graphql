<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

use function count;

class CollectionFilterTest extends AbstractTest
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
                        'type' => $driver->connection($driver->type(Artist::class)),
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
        $query = '{ artist { edges { node { performances ( filter: {id: 2} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testNeq(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_neq: 2} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testlt(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_lt: 2} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testlte(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_lte: 2} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testgt(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_gt: 1} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testgte(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_gte: 2} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testisnull(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {venue_isnull: true} ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(5, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testbetween(): void
    {
        $query = '{ artist { edges { node { performances ( filter: {id_between: { from: 2, to: 3 } } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);



        $query = '{ artist { edges { node { performances ( filter: {performanceDate_between: { from: "1995-02-21T00:00:00+00:00" to: "1995-07-09T00:00:00+00:00" } } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testcontains(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { venue_contains: "ill" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function teststartwith(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { venue_startswith: "Soldier" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(4, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testendswith(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { venue_endswith: "University" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(3, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testin(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { id_in: [1,2,3] } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testnotin(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { id_notin: [3,4] } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testsort(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { id_sort: "desc" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(5, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);


        $query = '{ artist { edges { node { performances ( filter: {  venue_sort: "asc" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(5, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);


        $query = '{ artist { edges { node { performances ( filter: { venue_sort: "desc" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(4, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testfirst(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { _first: 2 } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(1, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testfirstafter(): void
    {
        $after = base64_encode((string) 1);
        $query = '{ artist { edges { node { performances ( filter: { _first: 2, _after:"' . $after . '" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(3, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testlast(): void
    {
        $query = '{ artist { edges { node { performances ( filter: { _last: 3 } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }

    public function testlastbefore(): void
    {
        $after = base64_encode((string) 4);
        $query = '{ artist { edges { node { performances ( filter: { _last: 2, _before:"' . $after . '" } ) { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($this->schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(3, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);
    }
}
