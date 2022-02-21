<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

/**
 * Recording
 */
class Recording
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance
     */
    private $performance;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
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
