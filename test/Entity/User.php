<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Filter\Password;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat;
use ApiSkeletonsTest\Doctrine\GraphQL\Hydrator\NamingStrategy\CustomNamingStrategy;

/**
 * User
 */
#[GraphQL\Entity(description: 'User', typeName: 'user', group: 'default')]
#[GraphQL\Entity(description: 'User', typeName: 'user', group: 'testNonDefaultGroup')]
#[GraphQL\Entity(description: 'User', typeName: 'user', group: 'testPasswordFilter', filters: ['password' => ['filter' => Password::class]])]
#[GraphQL\Entity(group: 'NamingStrategyTest', namingStrategy: CustomNamingStrategy::class)]
#[GraphQL\Entity(group: 'CustomFieldStrategyTest')]
class User
{
    /**
     * @var string
     */
    #[GraphQL\Field(description: 'User name', group: 'default')]
    #[GraphQL\Field(description: 'User name', group: 'testNonDefaultGroup')]
    #[GraphQL\Field(description: 'User name', group: 'testPasswordFilter')]
    #[GraphQL\Field(group: 'NamingStrategyTest')]
    #[GraphQL\Field(group: 'CustomFieldStrategyTest', strategy: ToBoolean::class)]
    private $name;

    /**
     * @var string
     */
    #[GraphQL\Field(description: 'User email', group: 'default')]
    #[GraphQL\Field(group: 'NamingStrategyTest')]
    private $email;

    /**
     * @var string
     */
    #[GraphQL\Field(description: 'User password', group: 'default')]
    #[GraphQL\Field(description: 'User password', group: 'testPasswordFilter')]
    private $password;

    /**
     * @var int
     */
    #[GraphQL\Field(description: 'Primary key', group: 'default')]
    #[GraphQL\Field(description: 'Primary key', group: 'testNonDefaultGroup')]
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[GraphQL\Association(description: 'Recordings', strategy: AssociationDefault::class)]
    private $recordings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recordings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
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
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * Add recording.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording $recording
     *
     * @return User
     */
    public function addRecording(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording $recording)
    {
        $this->recordings[] = $recording;

        return $this;
    }

    /**
     * Remove recording.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording $recording
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRecording(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording $recording)
    {
        return $this->recordings->removeElement($recording);
    }

    /**
     * Get recordings.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecordings()
    {
        return $this->recordings;
    }
}
