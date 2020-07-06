<?php

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use GraphQL\Type\Definition\Type;

class Between extends AbstractFilterType
{
    public function __construct(array $config = [])
    {
        $config['fields'] = $config['fields'] ?? [];

        $defaultFieldConfig = [
            'field' => [
                'name' => 'field',
                'type' => Type::string(),
            ],
            'where' => [
                'name' => 'where',
                'type' => Type::string(),
                'defaultValue' => 'and',
            ],
            'format' => [
                'name' => 'format',
                'type' => Type::string(),
                'defaultValue' => 'Y-m-d\TH:i:sP',
            ],
        ];

        $config['fields'] = array_merge($config['fields'], $defaultFieldConfig);

        parent::__construct($config);
    }
}
