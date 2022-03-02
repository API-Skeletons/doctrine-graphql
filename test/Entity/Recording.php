<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;

/**
 * Recording
 */
#[GraphQL\Entity(typeName: 'recording', description: 'Performance recordings')]
#[GraphQL\Entity(typeName: 'entitytestrecording', description: 'Entity Test Recordings', group: 'entityTest')]
#[GraphQL\Entity(group: 'CustomFieldStrategyTest')]
class Recording
{
    /**
     * @var string
     */
    #[GraphQL\Field(description: 'Source')]
    #[GraphQL\Field(description: 'Entity Test Source', group: 'entityTest')]
    #[GraphQL\Field(group: 'CustomFieldStrategyTest')]
    private $source;

    /**
     * @var int
     */
    #[GraphQL\Field(description: 'Primary key')]
    #[GraphQL\Field(description: 'Entity Test ID', group: 'entityTest')]
    private $id;

    /**
     * @var \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance
     */
    #[GraphQL\Association(description: 'Performance entity')]
    #[GraphQL\Association(description: 'Entity Test Performance', group: 'entityTest')]
    private $performance;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[GraphQL\Association(description: 'Users')]
    #[GraphQL\Association(description: 'Entity Test Users', group: 'entityTest')]
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return Recording
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
     * Set performance.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance
     *
     * @return Recording
     */
    public function setPerformance(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance $performance)
    {
        $this->performance = $performance;

        return $this;
    }

    /**
     * Get performance.
     *
     * @return \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance
     */
    public function getPerformance()
    {
        return $this->performance;
    }

    /**
     * Add user.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\User $user
     *
     * @return Recording
     */
    public function addUser(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\User $user)
    {
        return $this->users->removeElement($user);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
