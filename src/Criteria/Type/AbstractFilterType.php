<?php

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use ReflectionObject;
use GraphQL\Type\Definition\InputObjectType;

abstract class AbstractFilterType extends InputObjectType
{
    public function __construct(array $config = [])
    {
        $config['name'] = 'fc' . uniqid();
        parent::__construct($config);
    }
}
