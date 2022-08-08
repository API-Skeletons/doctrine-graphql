<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Type;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Type\Date;
use ApiSkeletons\Doctrine\GraphQL\Type\Time;
use ApiSkeletons\Doctrine\GraphQL\Type\TimeImmutable;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\TypeTest;
use DateTime as PHPDateTime;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

class TimeImmutableTest extends AbstractTest
{
    public function testParseValue(): void
    {
        $dateTimeType = new TimeImmutable();
        $control = PHPDateTime::createFromFormat('Y-m-d\TH:i:s.uP', '2020-03-01T20:12:15.123456+00:00');
        $result = $dateTimeType->parseValue('20:12:15.123456');

        $this->assertEquals($control->format('H:i:s.u'), $result->format('H:i:s.u'));
    }

    public function testParseValueInvalid(): void
    {
        $this->expectException(Error::class);

        $dateType = new TimeImmutable();
        $result = $dateType->parseValue(true);
    }

    public function testBetween(): void
    {
        $driver = new Driver($this->getEntityManager(), new Config([
            'group' => 'DataTypesTest',
        ]));
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

        $query = '{ typetest ( filter: { testTimeImmutable_between: { from: "19:15:10.000000" to: "21:00:00.000000" } } ) { edges { node { id testDate } } } }';
        $result = GraphQL::executeQuery($schema, $query);

        $data = $result->toArray()['data'];

        $this->assertEquals(1, count($data['typetest']['edges']));
        $this->assertEquals(1, $data['typetest']['edges'][0]['node']['id']);
    }
}
