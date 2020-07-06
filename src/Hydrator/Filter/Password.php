<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Filter;

use Laminas\Hydrator\Filter\FilterInterface;

class Password implements FilterInterface
{
    public function filter($field)
    {
        $excludeFields = [
            'password',
            'secret',
        ];

        return (! in_array($field, $excludeFields));
    }
}
