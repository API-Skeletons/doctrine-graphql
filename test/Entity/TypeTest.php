<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToJson;

/**
 * TypeTest
 */
#[GraphQL\Entity(typeName: 'typeTest', description: 'Type test')]
#[GraphQL\Entity(group: 'DataTypesTest')]
class TypeTest
{
    /**
     * @var int
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testInt;

    /**
     * @var \DateTime
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testDateTime;

    /**
     * @var float
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testFloat;

    /**
     * @var bool
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testBool;

    /**
     * @var string
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testText;

    /**
     * @var int
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    private $id;

    #[GraphQL\Field(group: 'DataTypesTest')]
    private $testArray = [];

    public function setTestArray(array $value)
    {
        $this->testArray = $value;

        return $this;
    }

    public function getTestArray(): ?array
    {
        return $this->testArray;
    }

    /**
     * Set testInt.
     *
     * @param int $testInt
     *
     * @return TypeTest
     */
    public function setTestInt($testInt)
    {
        $this->testInt = $testInt;

        return $this;
    }

    /**
     * Get testInt.
     *
     * @return int
     */
    public function getTestInt()
    {
        return $this->testInt;
    }

    /**
     * Set testDateTime.
     *
     * @param \DateTime $testDateTime
     *
     * @return TypeTest
     */
    public function setTestDateTime($testDateTime)
    {
        $this->testDateTime = $testDateTime;

        return $this;
    }

    /**
     * Get testDateTime.
     *
     * @return \DateTime
     */
    public function getTestDateTime()
    {
        return $this->testDateTime;
    }

    /**
     * Set testFloat.
     *
     * @param float $testFloat
     *
     * @return TypeTest
     */
    public function setTestFloat($testFloat)
    {
        $this->testFloat = $testFloat;

        return $this;
    }

    /**
     * Get testFloat.
     *
     * @return float
     */
    public function getTestFloat()
    {
        return $this->testFloat;
    }

    /**
     * Set testBool.
     *
     * @param bool $testBool
     *
     * @return TypeTest
     */
    public function setTestBool($testBool)
    {
        $this->testBool = $testBool;

        return $this;
    }

    /**
     * Get testBool.
     *
     * @return bool
     */
    public function getTestBool()
    {
        return $this->testBool;
    }

    /**
     * Set testText.
     *
     * @param string $testText
     *
     * @return TypeTest
     */
    public function setTestText($testText)
    {
        $this->testText = $testText;

        return $this;
    }

    /**
     * Get testText.
     *
     * @return string
     */
    public function getTestText()
    {
        return $this->testText;
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
}
