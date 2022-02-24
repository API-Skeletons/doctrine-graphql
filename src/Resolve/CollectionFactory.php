<?php

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use GraphQL\Type\Definition\ResolveInfo;

class CollectionFactory
{
    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __invoke(Entity $entity): \Closure
    {
        return function($source, $args, $context, ResolveInfo $resolveInfo) use ($entity) {
            $fieldResolver = $this->driver->getFieldResolver();
            $collection = $fieldResolver($source, $args, $context, $resolveInfo);

            $filter = $args['filter'] ?? [];
            $filterArray = [];
            $orderByArray = [];
            $skip = 0;
            $limit = $this->driver->getConfig()->getLimit();

            foreach ($filter as $field => $value) {
                if ($field === '_skip') {
                    $skip = $value;

                    continue;
                }

                if ($field === '_limit') {
                    if ($value <= $limit) {
                        $limit = $value;

                        continue;
                    }
                }
            }

            // Create better colllection filtering tool

        };
    }
}
