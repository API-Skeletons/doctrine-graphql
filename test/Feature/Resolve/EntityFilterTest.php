<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

use function count;

class EntityFilterTest extends AbstractTest
{
    private array $schemas = [];

    public function schemaProvider(): array
    {
        parent::setUp();

        $schemas = [];

        $driver = new Driver($this->getEntityManager());
        $schemas[] = [new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'performance' => [
                        'type' => $driver->connection($driver->type(Performance::class)),
                        'args' => [
                            'filter' => $driver->filter(Performance::class),
                        ],
                        'resolve' => $driver->resolve(Performance::class),
                    ],
                ],
            ]),
        ])];

        return $schemas;
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testeq(Schema $schema): void
    {
        $query = '{ performance ( filter: {id: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(2, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testneq(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 id_neq: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testlt(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 id_lt: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testlte(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 id_lte: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testgt(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 id_gt: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['performance']['edges']));
        $this->assertEquals(3, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testgte(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 id_gte: 2} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(4, count($data['performance']['edges']));
        $this->assertEquals(2, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testisnull(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 venue_isnull: true} ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(5, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testbetween(Schema $schema): void
    {
        $query = '{ performance ( filter: {artist: 1 performanceDate_between: { from: "1995-02-21T00:00:00+00:00" to: "1995-07-09T00:00:00+00:00" } } ) { edges { node { id performanceDate } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);


        $query = '{ performance ( filter: { id_between: { from: 2 to: 3 } } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(2, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testcontains(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1 venue_contains: "ill" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(2, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function teststartswith(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1 venue_startswith: "Soldier" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(4, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testendswith(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1 venue_endswith: "University" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['performance']['edges']));
        $this->assertEquals(3, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testin(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1  id_in: [1,2,3] } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testnotin(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1  id_notin: [3,4] } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testsort(Schema $schema): void
    {
        $query = '{ performance ( filter: { artist: 1  id_sort: "desc" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['performance']['edges']));
        $this->assertEquals(5, $data['performance']['edges'][0]['node']['id']);



        $query = '{ performance ( filter: { artist: 1 venue_sort: "asc" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['performance']['edges']));
        $this->assertEquals(5, $data['performance']['edges'][0]['node']['id']);


        $query = '{ performance ( filter: { artist: 1 venue_sort: "desc" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(5, count($data['performance']['edges']));
        $this->assertEquals(4, $data['performance']['edges'][0]['node']['id']);
    }

    // -------------------------
    /**
     * @dataProvider schemaProvider
     */
    public function testfirst(Schema $schema): void
    {
        $query = '{ performance ( filter: { _first: 2 } ) { edges { node { id } } } }';

        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(1, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testfirstafter(Schema $schema): void
    {
        $after = base64_encode((string) 1);
        $query = '{ performance ( filter: { _first: 2, _after:"' . $after . '" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(3, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testlast(Schema $schema): void
    {
        $query = '{ performance ( filter: { _last: 3 } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(3, count($data['performance']['edges']));
        $this->assertEquals(5, $data['performance']['edges'][0]['node']['id']);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testlastbefore(Schema $schema): void
    {
        $after = base64_encode((string) 4);
        $query = '{ performance ( filter: { _last: 2, _before:"' . $after . '" } ) { edges { node { id } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(2, count($data['performance']['edges']));
        $this->assertEquals(3, $data['performance']['edges'][0]['node']['id']);
    }
}
