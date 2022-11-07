<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 */
#[GraphQL\Entity(typeName: 'artist', description: 'Artists')]
#[GraphQL\Entity(group: 'ExcludeCriteriaTest')]
#[GraphQL\Entity(group: 'TypeNameTest')]
#[ORM\Entity]
class Artist
{
    #[GraphQL\Field(description: 'Artist name')]
    #[GraphQL\Field(group: 'ExcludeCriteriaTest')]
    #[GraphQL\Field(group: 'TypeNameTest')]
    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[GraphQL\Field(description: 'Primary key')]
    #[GraphQL\Field(group: 'ExcludeCriteriaTest')]
    #[GraphQL\Field(group: 'TypeNameTest')]
    #[ORM\Id]
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /** @var Collection<id, Performance> */
    #[GraphQL\Association(description: 'Performances')]
    #[GraphQL\Association(group: 'ExcludeCriteriaTest', excludeCriteria: ['neq'])]
    #[ORM\OneToMany(targetEntity: 'ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance', mappedBy: 'artist')]
    private Collection $performances;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->performances = new ArrayCollection();
    }

    /**
     * Set name.
     */
    public function setName(string $name): Artist
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Add performance.
     */
    public function addPerformance(Performance $performance): Artist
    {
        $this->performances[] = $performance;

        return $this;
    }

    /**
     * Remove performance.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePerformance(Performance $performance): bool
    {
        return $this->performances->removeElement($performance);
    }

    /**
     * Get performances.
     *
     * @return Collection<id, Performance>
     */
    public function getPerformances(): Collection
    {
        return $this->performances;
    }
}
