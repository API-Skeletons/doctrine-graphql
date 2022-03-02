<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Criteria;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;

class CriteriaFactoryTest extends AbstractTest
{
    public function testExcludeCriteria(): void
    {
        $config = new Config([
            'group' => 'ExcludeCriteriaTest',
        ]);

        $driver = new Driver($this->getEntityManager(), $config);

        $filter = $driver->filter(Artist::class);

        $this->assertSame($filter, $driver->filter(Artist::class));
    }
}
