<?php

namespace ApiSkeletons\Doctrine\GraphQL\Documentation;

class HydratorConfigurationDocumentationProvider implements
    DocumentationProviderInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getEntity($entityClassName, array $options)
    {
        $hydratorAlias = 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);
        // @codingStandardsIgnoreStart
        $config = $this->config['apiskeletons-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']] ?? null;
        // @codingStandardsIgnoreEnd

        return $config['documentation']['_entity'] ?? null;
    }

    public function getField($entityClassName, $fieldName, array $options)
    {
        $hydratorAlias = 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);
        // @codingStandardsIgnoreStart
        $config = $this->config['apiskeletons-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']] ?? null;
        // @codingStandardsIgnoreEnd

        return $config['documentation'][$fieldName] ?? null;
    }
}
