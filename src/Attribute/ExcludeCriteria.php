<?php

namespace ApiSkeletons\Doctrine\GraphQL\Attribute;

use ApiSkeletons\Doctrine\GraphQL\Criteria\Filters;

trait ExcludeCriteria
{
    /** @return string[] */
    public function getExcludeCriteria(): array
    {
        if ($this->includeCriteria && $this->exccludeCriteria) {
            throw new \Exception('includeCriteria and excludeCriteria are mutually exclusive.');
        }

        if ($this->includeCriteria) {
            $this->excludeCriteria = array_diff(Filters::toArray(), $this->includeCriteria);
        } else if ($this->excludeCriteria) {
            $this->excludeCriteria = array_intersect(Filters::toArray(), $this->excludeCriteria);
        }

        return $this->excludeCriteria;
    }
}

