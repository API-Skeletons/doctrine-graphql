<?php

namespace ApiSkeletons\Doctrine\GraphQL;

use Exception;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\ModuleManager\ModuleManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\GraphQL;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    InitProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConfig()
    {
        $configProvider = new ConfigProvider();

        return [
            'service_manager' => $configProvider->getDependencyConfig(),
            'hydrators' => $configProvider->getHydratorConfig(),
            'controllers' => $configProvider->getControllerConfig(),
            'console' => [
                'router' => $configProvider->getConsoleRouterConfig(),
            ],
            'apiskeletons-doctrine-graphql-type' => $configProvider->getDoctrineGraphQLTypeConfig(),
            'apiskeletons-doctrine-graphql-filter' => $configProvider->getDoctrineGraphQLFilterConfig(),
            'apiskeletons-doctrine-graphql-criteria' => $configProvider->getDoctrineGraphQLCriteriaConfig(),
            'apiskeletons-doctrine-graphql-resolve' => $configProvider->getDoctrineGraphQLResolveConfig(),
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            'graphql:config-skeleton [--hydrator-sections=] [--object-manager=]'
                => 'Create GraphQL configuration skeleton',
            ['<hydrator-sections>', 'A comma delimited list of sections to generate.'],
            ['<object-manager>', 'Defaults to doctrine.entitymanager.orm_default.'],
        ];
    }

    public function init(ModuleManagerInterface $manager)
    {
        // @codeCoverageIgnoreStart
        if (! $manager instanceof ModuleManager) {
            throw new Exception('Invalid module manager');
        }
        // @codeCoverageIgnoreEnd

        $sm = $manager->getEvent()->getParam('ServiceManager');
        $serviceListener = $sm->get('ServiceListener');

        $serviceListener->addServiceManager(
            Type\TypeManager::class,
            'apiskeletons-doctrine-graphql-type',
            ObjectType::class,
            'getApiSkeletonsDoctrineGraphQLTypeConfig'
        );

        $serviceListener->addServiceManager(
            Filter\FilterManager::class,
            'apiskeletons-doctrine-graphql-filter',
            InputObjectType::class,
            'getApiSkeletonsDoctrineGraphQLFilterConfig'
        );

        $serviceListener->addServiceManager(
            Criteria\CriteriaManager::class,
            'apiskeletons-doctrine-graphql-criteria',
            InputObjectType::class,
            'getApiSkeletonsDoctrineGraphQLCriteriaConfig'
        );

        $serviceListener->addServiceManager(
            Resolve\ResolveManager::class,
            'apiskeletons-doctrine-graphql-resolve',
            'function',
            'getApiSkeletonsDoctrineGraphQLResolveConfig'
        );
    }

    public function onBootstrap(EventInterface $event)
    {
        $fieldResolver = $event->getParam('application')
            ->getServiceManager()
            ->get(Field\FieldResolver::class)
            ;

        GraphQL::setDefaultFieldResolver($fieldResolver);
    }
}
