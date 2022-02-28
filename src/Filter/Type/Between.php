<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Filter\Type;

use GraphQL\Type\Definition\Type;

use function array_merge;

class Between extends AbstractFilterType
{
    /**
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
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
