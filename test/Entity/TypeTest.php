<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToJson;
use Doctrine\ORM\Mapping as ORM;

/**
 * TypeTest
 */
#[GraphQL\Entity(typeName: 'typeTest', description: 'Type test')]
#[GraphQL\Entity(group: 'DataTypesTest')]
#[GraphQL\Entity(group: 'CustomTypeTest')]
#[ORM\Entity]
class TypeTest
{
    /**
     * @var int
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "integer", nullable: false)]
    private $testInt;

    /**
     * @var \DateTime
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "datetime", nullable: false)]
    private $testDateTime;

    /**
     * @var float
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[GraphQL\Field(group: 'CustomTypeTest', type: "customType")]
    #[ORM\Column(type: "float", nullable: false)]
    private $testFloat;

    /**
     * @var bool
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "boolean", nullable: false)]
    private $testBool;

    /**
     * @var string
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "text", nullable: false)]
    private $testText;

    /**
     * @var int
     */
    #[GraphQL\Field]
    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private $id;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "array", nullable: false)]
    private $testArray = [];

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "bigint", nullable: false)]
    private $testBigint;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "date_immutable", nullable: false)]
    private $testDateImmutable;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "date", nullable: false)]
    private $testDate;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "datetime_immutable", nullable: false)]
    private $testDateTimeImmutable;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "datetimetz", nullable: false)]
    private $testDateTimeTZ;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "datetimetz_immutable", nullable: false)]
    private $testDateTimeTZImmutable;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "time_immutable", nullable: false)]
    private $testTimeImmutable;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "time", nullable: false)]
    private $testTime;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "decimal", nullable: false)]
    private $testDecimal;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "guid", nullable: false)]
    private $testGuid;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "json", nullable: false)]
    private $testJson;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "simple_array", nullable: false)]
    private $testSimpleArray;

    #[GraphQL\Field(group: 'DataTypesTest')]
    #[ORM\Column(type: "smallint", nullable: false)]
    private $testSmallInt;

    /**
     * @return mixed
     */
    public function getTestBigint()
    {
        return $this->testBigint;
    }

    /**
     * @param mixed $testBigint
     */
    public function setTestBigint($testBigint): self
    {
        $this->testBigint = $testBigint;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDate()
    {
        return $this->testDate;
    }

    /**
     * @param mixed $testDate
     */
    public function setTestDate($testDate): self
    {
        $this->testDate = $testDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDateImmutable()
    {
        return $this->testDateImmutable;
    }

    /**
     * @param mixed $testDateImmutable
     */
    public function setTestDateImmutable($testDateImmutable): self
    {
        $this->testDateImmutable = $testDateImmutable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDateTimeImmutable()
    {
        return $this->testDateTimeImmutable;
    }

    /**
     * @param mixed $testDateTimeImmutable
     */
    public function setTestDateTimeImmutable($testDateTimeImmutable): self
    {
        $this->testDateTimeImmutable = $testDateTimeImmutable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDateTimeTZ()
    {
        return $this->testDateTimeTZ;
    }

    /**
     * @param mixed $testDateTimeTZ
     */
    public function setTestDateTimeTZ($testDateTimeTZ): self
    {
        $this->testDateTimeTZ = $testDateTimeTZ;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDateTimeTZImmutable()
    {
        return $this->testDateTimeTZImmutable;
    }

    /**
     * @param mixed $testDateTimeTZImmutable
     */
    public function setTestDateTimeTZImmutable($testDateTimeTZImmutable): self
    {
        $this->testDateTimeTZImmutable = $testDateTimeTZImmutable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestDecimal()
    {
        return $this->testDecimal;
    }

    /**
     * @param mixed $testDecimal
     */
    public function setTestDecimal($testDecimal): self
    {
        $this->testDecimal = $testDecimal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestGuid()
    {
        return $this->testGuid;
    }

    /**
     * @param mixed $testGuid
     */
    public function setTestGuid($testGuid): self
    {
        $this->testGuid = $testGuid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestJson()
    {
        return $this->testJson;
    }

    /**
     * @param mixed $testJson
     */
    public function setTestJson($testJson): self
    {
        $this->testJson = $testJson;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestSimpleArray()
    {
        return $this->testSimpleArray;
    }

    /**
     * @param mixed $testSimpleArray
     */
    public function setTestSimpleArray($testSimpleArray): self
    {
        $this->testSimpleArray = $testSimpleArray;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestSmallInt()
    {
        return $this->testSmallInt;
    }

    /**
     * @param mixed $testSmallInt
     */
    public function setTestSmallInt($testSmallInt): self
    {
        $this->testSmallInt = $testSmallInt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestTime()
    {
        return $this->testTime;
    }

    /**
     * @param mixed $testTime
     */
    public function setTestTime($testTime): self
    {
        $this->testTime = $testTime;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestTimeImmutable()
    {
        return $this->testTimeImmutable;
    }

    /**
     * @param mixed $testTimeImmutable
     */
    public function setTestTimeImmutable($testTimeImmutable): self
    {
        $this->testTimeImmutable = $testTimeImmutable;

        return $this;
    }

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
