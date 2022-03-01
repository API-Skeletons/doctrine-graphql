<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;

/**
 * Artist
 */
#[GraphQL\Entity(typeName: 'artist', description: 'Artists')]
#[GraphQL\Entity(group: 'ExcludeCriteriaTest')]
class Artist
{
    /**
     * @var string
     */
    #[GraphQL\Field(description: 'Artist name')]
    #[GraphQL\Field(group: 'ExcludeCriteriaTest')]
    private $name;

    /**
     * @var int
     */
    #[GraphQL\Field(description: 'Primary key')]
    #[GraphQL\Field(group: 'ExcludeCriteriaTest')]
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[GraphQL\Association(description: 'Performances')]
    #[GraphQL\Association(group: 'ExcludeCriteriaTest', excludeCriteria: ['neq'])]
    private $performances;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->performances = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Artist
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add performance.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance
     *
     * @return Artist
     */
    public function addPerformance(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance)
    {
        $this->performances[] = $performance;

        return $this;
    }

    /**
     * Remove performance.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePerformance(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance)
    {
        return $this->performances->removeElement($performance);
    }

    /**
     * Get performances.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerformances()
    {
        return $this->performances;
    }
}
