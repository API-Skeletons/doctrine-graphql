<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

class CriteriaTypeCollisionTest extends AbstractTest
{
    public function testCriteriaTypeCollision(): void
    {
        $driver1 = new Driver($this->getEntityManager());
        $driver2 = new Driver($this->getEntityManager(), new Config(['group' => 'ExcludeCriteriaTest']));

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'performance1' => [
                        'type' => $driver1->connection($driver1->type(Performance::class)),
                        'args' => [
                            'filter' => $driver1->filter(Performance::class),
                            'pagination' => $driver1->pagination(),
                        ],
                        'resolve' => $driver1->resolve(Performance::class),
                    ],
                    'performance2' => [
                        'type' => $driver2->connection($driver2->type(Performance::class)),
                        'args' => [
                            'filter' => $driver2->filter(Performance::class),
                            'pagination' => $driver2->pagination(),
                        ],
                        'resolve' => $driver2->resolve(Performance::class),
                    ],
                ],
            ]),
        ]);

        $query  = '{ one: performance1 ( filter: { id: { eq: 2 } } ) { edges { node { id  } } }, two: performance2 ( filter: { id: { eq: 2 } } ) { edges { node { id  } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals($data['one']['edges'][0]['node']['id'], $data['two']['edges'][0]['node']['id']);
        $this->assertSame(
            $driver1->get(TypeManager::class)->get('PageInfo'),
            $driver2->get(TypeManager::class)->get('PageInfo'),
        );
        $this->assertSame(
            $driver1->get(TypeManager::class),
            $driver2->get(TypeManager::class),
        );
    }
}
