<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\TypeTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class FieldStrategyTest extends AbstractTest
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

        $query = '{ user { name recordings { source } } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();
        foreach ($output['data']['user'] as $user) {
            $this->assertEquals(1, $user['name']);
        }
    }

    public function testNullifyOwningAssociation(): void
    {
        $driver = new Driver($this->getEntityManager());

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

        $query = '{ user { name recordings { source } } }';

        $result = GraphQL::executeQuery($schema, $query);

        foreach ($result->errors as $error) {
            $this->assertInstanceOf(Error::class, $error);
            $this->assertEquals('Query is barred by Nullify Owning Association', $error->getMessage());
        }
    }
}
