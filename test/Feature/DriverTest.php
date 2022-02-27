<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use Psr\Container\ContainerInterface;

class DriverTest extends AbstractTest
{
    public function testCreateDriverWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config = new Config([]);

        $driver = new Driver($container, $this->getEntityManager(), $config);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->getMetadata());
    }

    public function testCreateDriverWithConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config = new Config([
            'group' => 'default',
            'useHydratorCache' => true,
            'limit' => 1000,
            'usePartials' => true,
        ]);

        $driver = new Driver($container, $this->getEntityManager(), $config);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertInstanceOf(Metadata::class, $driver->getMetadata());
    }
}
