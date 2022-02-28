<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function array_merge;

class Between extends InputObjectType
{
    /**
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        $config['fields'] ??= [];

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

        $config['name'] = 'fc' . uniqid();
        parent::__construct($config);
    }
}
