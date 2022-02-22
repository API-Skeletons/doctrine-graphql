<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Laminas\Hydrator\Filter\PropertyName;
use Laminas\Hydrator\AbstractHydrator;
use Laminas\Hydrator\Filter\FilterComposite;

class Entity
{
    use Trait\Constructor;

    public function getDocs(): string
    {
        return $this->metadata['docs'];
    }

    public function getHydrator(): AbstractHydrator
    {
        $hydratorClass = $this->metadata['hydrator'];

        if ($hydratorClass !== 'default') {
            return $this->container->get($hydratorClass);
        }

        // If a default hydrator is used, build the hydrator here
        $hydrator = new DoctrineObject($this->entityManager, $this->metadata['byValue']);

        // Filter for an include list of properties
        $allowedProperties = array_keys($this->metadata['fields']);
        $filter = new PropertyName($allowedProperties, false);

        $hydrator->addFilter('metadata', $filter, FilterComposite::CONDITION_AND);

        return $hydrator;
    }
}
