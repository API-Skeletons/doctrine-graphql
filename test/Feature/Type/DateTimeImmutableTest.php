<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\DateTimeImmutable;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\TypeTest;
use DateTime as PHPDateTime;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

use function count;

class DateTimeImmutableTest extends AbstractTest
{
    public function testParseValue(): void
    {
        $dateImmutableType = new DateTimeImmutable();
        $control           = PHPDateTime::createFromFormat('Y-m-d\TH:i:sP', '2020-03-01T00:00:00+00:00');
        $result            = $dateImmutableType->parseValue('2020-03-01T00:00:00+00:00');

        $this->assertEquals($control, $result);
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(Error::class);

        $dateType = new DateTimeImmutable();
        $result   = $dateType->parseValue(true);
    }

    public function testBetween(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config(['group' => 'DataTypesTest']));
        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'typetest' => [
                        'type' => $driver->connection($driver->type(TypeTest::class)),
                        'args' => [
                            'filter' => $driver->filter(TypeTest::class),
                        ],
                        'resolve' => $driver->resolve(TypeTest::class),
                    ],
                ],
            ]),
        ]);

        $now    = (new PHPDateTime())->format('c');
        $query  = '{ typetest ( filter: { testDateTimeImmutable_between: { from: "2022-08-06T00:00:00+00:00" to: "' . $now . '" } } ) { edges { node { id testDateTimeImmutable } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['typetest']['edges']));
        $this->assertEquals(1, $data['typetest']['edges'][0]['node']['id']);
    }
}
