<?php

namespace ApiSkeletons\Doctrine\GraphQL\Documentation;

interface DocumentationProviderInterface
{
    public function getField($entityClassName, $fieldName, array $config);
    public function getEntity($entityClassName, array $config);
}
