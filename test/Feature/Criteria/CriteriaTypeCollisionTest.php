<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class CriteriaTypeCollisionTest extends AbstractTest
{
    public function testExcludeCriteria(): void
    {
        $config1 = new Config();

        $config2 = new Config(['group' => 'ExcludeCriteriaTest']);

        $driver1 = new Driver($this->getEntityManager(), $config1);
        $driver2 = new Driver($this->getEntityManager(), $config2);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'performance1' => [
                        'type' => Type::listOf($driver1->type(Performance::class)),
                        'args' => [
                            'filter' => $driver1->filter(Performance::class),
                        ],
                        'resolve' => $driver1->resolve(Performance::class),
                    ],
                    'performance2' => [
                        'type' => Type::listOf($driver2->type(Performance::class)),
                        'args' => [
                            'filter' => $driver2->filter(Performance::class),
                        ],
                        'resolve' => $driver2->resolve(Performance::class),
                    ],
                ],
            ]),
        ]);

        $query  = '{ one: performance1 ( filter: {id: 2} ) { id }, two: performance2 ( filter: {id: 2} ) { id }}';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals($data['one'][0]['id'], $data['two'][0]['id']);
    }
}
