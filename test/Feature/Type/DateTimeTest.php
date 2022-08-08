<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Type\DateTime;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use DateTime as PHPDateTime;
use GraphQL\Error\Error;

class DateTimeTest extends AbstractTest
{
    public function testParseValue(): void
    {
        $dateTimeType = new DateTime();
        $control      = PHPDateTime::createFromFormat('Y-m-d\TH:i:sP', '2020-03-01T00:00:00+00:00');
        $result       = $dateTimeType->parseValue('2020-03-01T00:00:00+00:00');

        $this->assertEquals($control, $result);
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(Error::class);

        $dateTimeType = new DateTime();
        $result       = $dateTimeType->parseValue(true);
    }
}
