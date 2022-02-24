<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Type\Entity;

class Metadata
{
    use Trait\Constructor;

    public function getEntity($entityClass): Entity
    {
        return new Entity(
            $this->container,
            $this->entityManager,
            $this->metadata[$entityClass]
        );
    }
}
