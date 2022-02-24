<?php

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\Context;

class Loader
{
    protected $typeManager;

    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    public function __invoke(string $name, Context $context = null) : Entity
    {
        $context = $context ?? new Context();

        return $this->typeManager->build($name, $context->toArray());
    }
}
