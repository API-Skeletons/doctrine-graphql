<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\GraphQL;

use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use Laminas\EventManager\Event as ZendEvent;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type;
use ApiSkeletons\Doctrine\GraphQL\Event;
use DbTest\Entity;

class EventsTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testQueryBuilderEvent($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $container = $this->getApplication()->getServiceManager();
        $events = $container->get('SharedEventManager');

        $events->attach(
            Event::class,
            Event::FILTER_QUERY_BUILDER,
            function(ZendEvent $event)
            {
                switch ($event->getParam('entityClassName')) {
                    case 'DbTest\Entity\Performance':
                        $event->getParam('queryBuilder')
                            ->andWhere('row.id = 1')
                            ;
                        break;
                    default:
                        break;
                }
            },
            100
        );

        $query = "{ performance { id } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testResolveEvent($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $container = $this->getApplication()->getServiceManager();
        $events = $container->get('SharedEventManager');
        $hydratorExtractTool = $container->get('ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');

        $events->attach(
            Event::class,
            Event::RESOLVE,
            function(ZendEvent $event) use ($hydratorExtractTool)
            {
                $object = $event->getParam('object');
                $arguments = $event->getParam('arguments');
                $context = $event->getParam('context');
                $hydratorAlias = $event->getParam('hydratorAlias');
                $objectManager = $event->getParam('objectManager');
                $entityClassName = $event->getParam('entityClassName');

                $results = $objectManager->getRepository($entityClassName)->findBy([
                    'attendance' => 2000,
                ]);

                $resultCollection = $hydratorExtractTool->extractToCollection($results, $hydratorAlias, $context);

                $event->stopPropagation(true);

                return $resultCollection;
            },
            100
        );

        $query = "{ performance { id attendance } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testResolvePostEvent($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $container = $this->getApplication()->getServiceManager();
        $events = $container->get('SharedEventManager');
        $hydratorExtractTool = $container->get('ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');

        $events->attach(
            Event::class,
            Event::RESOLVE_POST,
            function(ZendEvent $event) use ($hydratorExtractTool)
            {
                $objectManager = $event->getParam('objectManager');
                $entityClassName = $event->getParam('entityClassName');
                $resultCollection = $event->getParam('resultCollection');
                $context = $event->getParam('context');
                $hydratorAlias = $event->getParam('hydratorAlias');

                $results = $objectManager->getRepository($entityClassName)->findBy([
                    'attendance' => 2000,
                ]);

                $resultCollection->clear();
                foreach ($results as $key => $value) {
                    $resultCollection->add($hydratorExtractTool->extract($value, $hydratorAlias, $context));
                }

                $event->stopPropagation(true);

                return $resultCollection;
            },
            100
        );

        $query = "{ performance { id attendance } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }


    /**
     * @dataProvider eventDataProvider
     */
    public function testOverrideGraphQLTypeOnEntityTypeEvent($schemaName, $context)
    {
        $container = $this->getApplication()->getServiceManager();
        $events = $container->get('SharedEventManager');
        $config = $container->get('config');

        $events->attach(
            Event::class,
            Event::MAP_FIELD_TYPE,
            function(ZendEvent $event) use ($container, $config)
            {
                $hydratorAlias = $event->getParam('hydratorAlias');
                $options = $event->getParam('options');
                $fieldName = $event->getParam('fieldName');

                if ($hydratorAlias == 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Artist') {
                    if ($fieldName === 'alias') {
                        // Update all Artist alias to a multidimentional array
                        $hydratorConfig = $config['apiskeletons-doctrine-graphql-hydrator'][$hydratorAlias];
                        $objectManager =
                            $container->get($hydratorConfig[$options['hydrator_section']]['object_manager']);

                        $artist = $objectManager->getRepository(Entity\Artist::class)
                            ->find(1);

                        $artist->alias = ['multi' => ['dimentional' => 'array']];
                        $objectManager->flush();

                        $event->stopPropagation();

                        return Type::string();
                    }
                }
            },
            100
        );

        $schema = $this->getSchema($schemaName);
        $query = "{ artist ( filter: { id:1 } ) { id alias } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('{"multi":{"dimentional":"array"}}', $output['data']['artist'][0]['alias']);
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testOverrideGraphQLTypeOnFilterTypeAndCriteriaEvent($schemaName, $context)
    {
        $container = $this->getApplication()->getServiceManager();
        $events = $container->get('SharedEventManager');
        $config = $container->get('config');

        $events->attach(
            Event::class,
            Event::MAP_FIELD_TYPE,
            function(ZendEvent $event) use ($container, $config)
            {
                $hydratorAlias = $event->getParam('hydratorAlias');
                $options = $event->getParam('options');
                $fieldName = $event->getParam('fieldName');

                if ($hydratorAlias == 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Performance') {
                    if ($fieldName === 'attendance') {
                        // Update all Artist alias to a multidimentional array
                        $hydratorConfig = $config['apiskeletons-doctrine-graphql-hydrator'][$hydratorAlias];
                        $objectManager =
                            $container->get($hydratorConfig[$options['hydrator_section']]['object_manager']);

                        $artist = $objectManager->getRepository(Entity\Artist::class)
                            ->find(1);

                        $artist->alias = [1, 2, 3];
                        $objectManager->flush();

                        $event->stopPropagation();

                        return Type::listOf(Type::int());
                    }
                }
            },
            100
        );

        $schema = $this->getSchema($schemaName);
        $query = "{ performance ( filter: { id:1 } ) { id artist { alias } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals([1, 2, 3], $output['data']['performance'][0]['artist']['alias']);
    }
}
