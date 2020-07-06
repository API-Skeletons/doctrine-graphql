<?php

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

final class ResolveManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = ResolveManager::class;
}
