<?php

namespace ApiSkeletons\Doctrine\GraphQL\Filter;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

final class FilterManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = FilterManager::class;
}
