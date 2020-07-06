<?php

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter;

use Laminas\Hydrator\Filter\FilterInterface;

class FilterDefault implements FilterInterface
{
    public function filter($field)
    {
        $excludeFields = [
        ];

        return (! in_array($field, $excludeFields));
    }
}
