<?php
return [
    'apiskeletons-doctrine-graphql-hydrator' => [
        'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Artist' => [
            'default' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'alias' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'createdAt' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'partials' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'alias' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'createdAt' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'test' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'alias' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'createdAt' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'event' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'alias' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToJson::class,
                    'createdAt' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'eventTest' => [
                        'condition' => 'and',
                        'filter' => 'DbTest\Hydrator\Filter\EventTestFilter',
                    ],
                ],
            ],
        ],
        'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_User' => [
            'default' => [
                'entity_class' => \DbTest\Entity\User::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                    'password' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password::class,
                    ],
                ],
            ],
            'partials' => [
                'entity_class' => \DbTest\Entity\User::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                    'password' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password::class,
                    ],
                ],
            ],
            'test' => [
                'entity_class' => \DbTest\Entity\User::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                    'password' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password::class,
                    ],
                ],
            ],
        ],
        'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Address' => [
            'default' => [
                'entity_class' => \DbTest\Entity\Address::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'partials' => [
                'entity_class' => \DbTest\Entity\Address::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'test' => [
                'entity_class' => \DbTest\Entity\Address::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'address' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'user' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
        ],
        'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Performance' => [
            'default' => [
                'entity_class' => \DbTest\Entity\Performance::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'performanceDate' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'venue' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'attendance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'isTradable' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean::class,
                    'ticketPrice' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'partials' => [
                'entity_class' => \DbTest\Entity\Performance::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'performanceDate' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'venue' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'attendance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'isTradable' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean::class,
                    'ticketPrice' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'test' => [
                'entity_class' => \DbTest\Entity\Performance::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'performanceDate' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'venue' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'attendance' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'isTradable' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean::class,
                    'ticketPrice' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat::class,
                    'artist' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
        ],
    ],
];
