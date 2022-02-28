<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Hydrator;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use ApiSkeletonsTest\Doctrine\GraphQL\Hydrator\NamingStrategy\CustomNamingStrategy;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class NamingStrategyTest extends AbstractTest
{
    public function testNamingStrategy(): void
    {
        $config = new Config([
            'group' => 'NamingStrategyTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);
        $driver->getHydratorFactory()
            ->set(CustomNamingStrategy::class, new CustomNamingStrategy());

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

        $query = '{ user { name email } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        foreach ($output['data']['user'] as $user) {
            $this->assertNotEmpty($user['name']);
        }
    }
}
