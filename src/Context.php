<?php

namespace ApiSkeletons\Doctrine\GraphQL;

use Laminas\Stdlib\AbstractOptions;

/**
 * This class serves as the context array for
 * GraphQL.  Default values are set here
 */
class Context extends AbstractOptions
{
    protected $hydratorSection = 'default';
    protected $limit = 1000;
    protected $useHydratorCache = false;
    protected $usePartials = false;

    public function setHydratorSection(string $value)
    {
        $this->hydratorSection = $value;

        return $this;
    }

    public function getHydratorSection()
    {
        return $this->hydratorSection;
    }

    public function setLimit(int $value)
    {
        $this->limit = $value;

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getUseHydratorCache()
    {
        return $this->useHydratorCache;
    }

    public function setUseHydratorCache(bool $value)
    {
        $this->useHydratorCache = $value;

        return $this;
    }

    public function getUsePartials()
    {
        return $this->usePartials;
    }

    public function setUsePartials(bool $usePartials)
    {
        $this->usePartials = $usePartials;

        return $this;
    }
}
