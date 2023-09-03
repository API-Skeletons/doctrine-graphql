<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

use function base64_encode;
use function count;

class AttributeLimitTest extends AbstractTest
{
    public function testLimit(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'AttributeLimit']));
        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => $driver->connection($driver->type(Artist::class)),
                        'args' => [
                            'filter' => $driver->filter(Artist::class),
                            'pagination' => $driver->pagination(),
                        ],
                        'resolve' => $driver->resolve(Artist::class),
                    ],
                ],
            ]),
        ]);

        $query  = '{ artist ( filter: {id: 1}) { edges { node { performances { edges { node { id } } } } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        print_r($result->toArray());
        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['artist']['edges'][0]['node']['performances']['edges']));
        $this->assertEquals(2, $data['artist']['edges'][0]['node']['performances']['edges'][0]['node']['id']);


    }
}
