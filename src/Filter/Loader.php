<?php

namespace ApiSkeletons\Doctrine\GraphQL\Filter;

use ApiSkeletons\Doctrine\GraphQL\Context;

class Loader
{
    protected $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    public function __invoke(string $name, Context $context = null) : FilterType
    {
        $context = $context ?? new Context();

        return $this->filterManager->build($name, $context->toArray());
    }
}
