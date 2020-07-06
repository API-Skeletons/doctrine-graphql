<?php

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

use Laminas\Mvc\Service\AbstractPluginManagerFactory;

final class CriteriaManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = CriteriaManager::class;
}
