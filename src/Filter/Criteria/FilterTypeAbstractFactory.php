<?php

namespace ZF\Doctrine\GraphQL\Filter\Criteria;

use ArrayObject;
use DateTime;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use ZF\Doctrine\GraphQL\Type\TypeManager;
use ZF\Doctrine\GraphQL\Filter\Criteria\Type as FilterTypeNS;
use ZF\Doctrine\Criteria\Filter\Service\FilterManager;
use ZF\Doctrine\Criteria\OrderBy\Service\OrderByManager;

final class FilterTypeAbstractFactory implements
    AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    /**
     * Loop through all configured ORM managers and if the passed $requestedName
     * as entity name is managed by the ORM return true;
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        return isset($config['zf-doctrine-graphql-hydrator'][$hydratorAlias]);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : FilterType
    {
        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');
        $typeManager = $container->get(TypeManager::class);
        $filterManager = $container->get(FilterManager::class);
        $orderByManager = $container->get(OrderByManager::class);

        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias];

        $objectManager = $container->get($hydratorConfig['object_manager']);
        $hydrator = $hydratorManager->get($hydratorAlias);

        // Create an instance of the entity in order to get fields from the hydrator.
        $instantiator = new Instantiator();
        $entity = $instantiator->instantiate($requestedName);
        $entityFields = array_keys($hydrator->extract($entity));
        $references = [];

        $classMetadata = $objectManager->getClassMetadata($requestedName);

        foreach ($entityFields as $fieldName) {
            $graphQLType = null;
            try {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            } catch (MappingException $e) {
                // For all related data you cannot query on them from the current resource
                continue;
            }

            switch ($fieldMetadata['type']) {
                case 'tinyint':
                case 'smallint':
                case 'integer':
                case 'int':
                case 'bigint':
                    $graphQLType = Type::int();
                    break;
                case 'boolean':
                    $graphQLType = Type::boolean();
                    break;
                case 'decimal':
                case 'float':
                    $graphQLType = Type::float();
                    break;
                case 'string':
                case 'text':
                    $graphQLType = Type::string();
                    break;
                case 'datetime':
                    $graphQLType = Type::string();
                    break;
                default:
                    // Do not process unknown for now
                    $graphQLType = null;
                    break;
            }

            if ($graphQLType && $classMetadata->isIdentifier($fieldMetadata['fieldName'])) {
                $graphQLType = Type::id();
            }

            if ($graphQLType) {
                if ($orderByManager->has('field')) {
                    $fields[$fieldName . '_orderby'] = [
                        'name' => $fieldName . '_orderby',
                        'type' => Type::string(),
                        'description' => 'building...',
                    ];
                }

                // Add filters
                if ($filterManager->has('eq')) {
                    $fields[$fieldName] = [
                        'name' => $fieldName,
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];

                    $fields[$fieldName . '_eq'] = [
                        'name' => $fieldName . '_eq',
                        'type' => new FilterTypeNS\Equals(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType)
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('neq')) {
                    $fields[$fieldName . '_neq'] = [
                        'name' => $fieldName . '_neq',
                        'type' => new FilterTypeNS\NotEquals(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('lt')) {
                    $fields[$fieldName . '_lt'] = [
                        'name' => $fieldName . '_lt',
                        'type' => new FilterTypeNS\LessThan(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }
                if ($filterManager->has('lte')) {
                    $fields[$fieldName . '_lte'] = [
                        'name' => $fieldName . '_lte',
                        'type' => new FilterTypeNS\LessThanOrEquals(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('gt')) {
                    $fields[$fieldName . '_gt'] = [
                        'name' => $fieldName . '_gt',
                        'type' => new FilterTypeNS\GreaterThan(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('gte')) {
                    $fields[$fieldName . '_gte'] = [
                        'name' => $fieldName . '_gte',
                        'type' => new FilterTypeNS\GreaterThanOrEquals(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('in')) {
                    $fields[$fieldName . '_in'] = [
                        'name' => $fieldName . '_in',
                        'type' => new FilterTypeNS\In(['fields' => [
                            'values' => [
                                'name' => 'values',
                                'type' => Type::listOf(Type::nonNull($graphQLType)),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('notin')) {
                    $fields[$fieldName . '_notin'] = [
                        'name' => $fieldName . '_notin',
                        'type' => new FilterTypeNS\NotIn(['fields' => [
                            'values' => [
                                'name' => 'values',
                                'type' => Type::listOf(Type::nonNull($graphQLType)),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('startswith')) {
                    $fields[$fieldName . '_startswith'] = [
                        'name' => $fieldName . '_startswith',
                        'type' => new FilterTypeNS\StartsWith(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('endswith')) {
                    $fields[$fieldName . '_endswith'] = [
                        'name' => $fieldName . '_endswith',
                        'type' => new FilterTypeNS\EndsWith(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('contains')) {
                    $fields[$fieldName . '_contains'] = [
                        'name' => $fieldName . '_contains',
                        'type' => new FilterTypeNS\EndsWith(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('memberof')) {
                    $fields[$fieldName . '_memberof'] = [
                        'name' => $fieldName . '_memberof',
                        'type' => new FilterTypeNS\EndsWith(['fields' => [
                            'value' => [
                                'name' => 'value',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }
            }
        }

        $fields['_skip'] = [
            'name' => '_skip',
            'type' => Type::int(),
        ];
        $fields['_limit'] = [
            'name' => '_limit',
            'type' => Type::int(),
        ];

        return new FilterType([
            'name' => str_replace('\\', '_', $requestedName) . 'CriteriaFilter',
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);
    }
}