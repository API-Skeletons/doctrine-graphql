<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;

class Manager extends AbstractContainer
{
    public function __construct()
    {
        $this->set('datetime', new DateTime());
    }
}
