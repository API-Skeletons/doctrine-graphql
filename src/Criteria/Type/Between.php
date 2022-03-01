<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function array_merge;
use function uniqid;

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
        ];

        $config['fields'] = array_merge($config['fields'], $defaultFieldConfig);
        $config['name'] = 'fc' . uniqid();

        parent::__construct($config);
    }
}
