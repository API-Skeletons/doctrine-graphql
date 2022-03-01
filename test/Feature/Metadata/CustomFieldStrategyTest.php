<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use ApiSkeletonsTest\Doctrine\GraphQL\Hydrator\NamingStrategy\CustomNamingStrategy;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class CustomFieldStrategyTest extends AbstractTest
{
    public function testCustomFieldStrategy(): void
    {
        $config = new Config([
            'group' => 'CustomFieldStrategyTest',
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

#        print_r($driver->getMetadata()->getMetadataConfig());die();

        $query = '{ user { name } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();
        foreach ($output['data']['user'] as $user) {
            $this->assertEquals(1, $user['name']);
        }
    }
}
