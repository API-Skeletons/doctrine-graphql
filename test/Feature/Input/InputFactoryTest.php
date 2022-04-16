<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\User;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class InputFactoryTest extends AbstractTest
{
    public function testInputWithRequiredField(): void
    {
        $config = new Config([
            'group' => 'InputFactoryTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
            'mutation' => new ObjectType([
                'name' => 'mutation',
                'fields' => [
                    'testInput' => [
                        'type' => $driver->type(User::class),
                        'args' => [
                            'id' => Type::nonNull(Type::id()),
                            'input' => Type::nonNull($driver->input(User::class, ['name'])),
                        ],
                        'resolve' => function ($root, $args): User {
                            $user = $this->getEntityManager()->getRepository(User::class)
                                ->find($args['id']);

                            $user->setName($args['input']['name']);
                            $this->getEntityManager()->flush();

                            return $user;
                        },
                    ],
                ],
            ]),
        ]);

        $query = 'mutation {
            testInput(id: 1, input: { name: "inputTest" }) {
                id
                name
            }
        }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->getEntityManager()->clear();
        $user = $this->getEntityManager()->getRepository(User::class)
            ->find(1);

        $this->assertEquals('inputTest', $user->getName());
        $this->assertEquals(1, $output['data']['testInput']['id']);
        $this->assertEquals('inputTest', $output['data']['testInput']['name']);
    }

    public function testInputWithOptionalField(): void
    {
        $config = new Config([
            'group' => 'InputFactoryTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
            'mutation' => new ObjectType([
                'name' => 'mutation',
                'fields' => [
                    'testInput' => [
                        'type' => $driver->type(User::class),
                        'args' => [
                            'id' => Type::nonNull(Type::id()),
                            'input' => Type::nonNull($driver->input(User::class, [], ['name'])),
                        ],
                        'resolve' => function ($root, $args): User {
                            $user = $this->getEntityManager()->getRepository(User::class)
                                ->find($args['id']);

                            $user->setName($args['input']['name']);
                            $this->getEntityManager()->flush();

                            return $user;
                        },
                    ],
                ],
            ]),
        ]);

        $query = 'mutation {
            testInput(id: 1, input: { name: "inputTest" }) {
                id
                name
            }
        }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->getEntityManager()->clear();
        $user = $this->getEntityManager()->getRepository(User::class)
            ->find(1);

        $this->assertEquals('inputTest', $user->getName());
        $this->assertEquals(1, $output['data']['testInput']['id']);
        $this->assertEquals('inputTest', $output['data']['testInput']['name']);
    }

    public function testInputWithAllFieldsRequired(): void
    {
        $config = new Config([
            'group' => 'InputFactoryTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $schema = new Schema([
            'mutation' => new ObjectType([
                'name' => 'mutation',
                'fields' => [
                    'testInput' => [
                        'type' => $driver->type(User::class),
                        'args' => [
                            'id' => Type::nonNull(Type::id()),
                            'input' => Type::nonNull($driver->input(User::class)),
                        ],
                        'resolve' => function ($root, $args): User {
                            $user = $this->getEntityManager()->getRepository(User::class)
                                ->find($args['id']);

                            $user->setName($args['input']['name']);
                            $this->getEntityManager()->flush();

                            return $user;
                        },
                    ],
                ],
            ]),
        ]);

        $query = 'mutation {
            testInput(id: 1, input: { name: "inputTest" email: "email" password: "password", id: 1}) {
                id
                name
            }
        }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->getEntityManager()->clear();
        $user = $this->getEntityManager()->getRepository(User::class)
            ->find(1);

        $this->assertEquals('inputTest', $user->getName());
        $this->assertEquals(1, $output['data']['testInput']['id']);
        $this->assertEquals('inputTest', $output['data']['testInput']['name']);
    }
}
