<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Filter\Type;

use GraphQL\Type\Definition\InputObjectType;

use function uniqid;

abstract class AbstractFilterType extends InputObjectType
{
    /**
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        $config['name'] = 'f' . uniqid();
        parent::__construct($config);
    }
}
