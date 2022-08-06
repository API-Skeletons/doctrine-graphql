<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use Doctrine\ORM\Mapping as ORM;

/**
 * Performance
 */
#[GraphQL\Entity(typeName: 'performance', description: 'Performances')]
#[GraphQL\Entity(group: 'ExcludeCriteriaTest', excludeCriteria: ['contains'])]
#[ORM\Entity]
class Performance
{
    /**
     * @var string|null
     */
    #[GraphQL\Field(description: 'Venue name')]
    #[GraphQL\Field(description: 'Venue name', group: 'ExcludeCriteriaTest')]
    #[ORM\Column(type: "string", nullable: true)]
    private $venue;

    /**
     * @var string|null
     */
    #[GraphQL\Field(description: 'City name')]
    #[ORM\Column(type: "string", nullable: true)]
    private $city;

    /**
     * @var string|null
     */
    #[GraphQL\Field(description: 'State name')]
    #[ORM\Column(type: "string", nullable: true)]
    private $state;

    /**
     * @var \DateTime
     */
    #[GraphQL\Field(description: 'Performance date')]
    #[ORM\Column(type: "datetime", nullable: false)]
    private $performanceDate;

    /**
     * @var int
     */
    #[GraphQL\Field(description: 'Primary key')]
    #[GraphQL\Field(group: 'ExcludeCriteriaTest')]
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    #[GraphQL\Association(description: 'Recordings by artist')]
    #[ORM\OneToMany(targetEntity: "ApiSkeletonsTest\Doctrine\GraphQL\Entity\Recording", mappedBy: "performance")]
    private $recordings;

    /**
     * @var \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist
     */
    #[GraphQL\Association(description: 'Artist entity')]
    #[ORM\ManyToOne(targetEntity: "ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist", inversedBy: "performances")]
    #[ORM\JoinColumn(name: "artist_id", referencedColumnName: "id", nullable: false)]
    private $artist;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recordings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set venue.
     *
     * @param string|null $venue
     *
     * @return Performance
     */
    public function setVenue($venue = null)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get venue.
     *
     * @return string|null
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Set city.
     *
     * @param string|null $city
     *
     * @return Performance
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state.
     *
     * @param string|null $state
     *
     * @return Performance
     */
    public function setState($state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set performanceDate.
     *
     * @param \DateTime $performanceDate
     *
     * @return Performance
     */
    public function setPerformanceDate($performanceDate)
    {
        $this->performanceDate = $performanceDate;

        return $this;
    }

    /**
     * Get performanceDate.
     *
     * @return \DateTime
     */
    public function getPerformanceDate()
    {
        return $this->performanceDate;
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
     * @return Performance
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

    /**
     * Set artist.
     *
     * @param \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist $artist
     *
     * @return Performance
     */
    public function setArtist(\ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist $artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist.
     *
     * @return \ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }
}
