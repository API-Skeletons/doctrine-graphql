<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class ExcludeCriteriaTest extends AbstractTest
{
    public function testExcludeCriteria(): void
    {
        $config = new Config([
            'group' => 'ExcludeCriteriaTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
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

        $query = '{ artist { performances ( filter: {venue_neq: "test"} ) { venue } } }';
        $result = GraphQL::executeQuery($schema, $query);

        foreach ($result->errors as $error) {
            $this->assertEquals('Field "venue_neq" is not defined by type '
                . 'ApiSkeletonsTest_Doctrine_GraphQL_Entity_Performance_Filter; Did '
                . 'you mean venue_in or venue_notin?', $error->getMessage());
        }

        $query = '{ artist { performances ( filter: {venue_contains: "test"} ) { venue } } }';
        $result = GraphQL::executeQuery($schema, $query);

        foreach ($result->errors as $error) {
            $this->assertEquals('Field "venue_contains" is not defined by type '
                . 'ApiSkeletonsTest_Doctrine_GraphQL_Entity_Performance_Filter; Did '
                . 'you mean venue_notin or venue_in?', $error->getMessage());
        }
    }
}
