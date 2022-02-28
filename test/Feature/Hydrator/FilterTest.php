<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Hydrator;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class FilterTest extends AbstractTest
{
    public function testPasswordFilter(): void
    {
        $config = new Config([
            'group' => 'testPasswordFilter',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'user' => [
                        'type' => Type::listOf($driver->type(User::class)),
                        'args' => [
                            'filter' => $driver->filter(User::class),
                        ],
                        'resolve' => $driver->resolve(User::class),
                    ],
                ],
            ]),
        ]);

        $query = '{ user { name password } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        foreach ($output['data']['user'] as $user) {
            $this->assertEmpty($user['password']);
        }
    }
}
