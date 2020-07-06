<?php

namespace ApiSkeletons\Doctrine\GraphQL\Field;

use Interop\Container\ContainerInterface;

class FieldResolverFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $hydratorExtractTool = $container->get('ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');
        $hydratorManager = $container->get('HydratorManager');

        return new FieldResolver($hydratorExtractTool, $hydratorManager);
    }
}
