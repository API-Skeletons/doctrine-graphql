<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

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
