<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\Connection;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletonsTest\Doctrine\GraphQL\Entity\Artist;

class TypeManagerTest extends AbstractTest
{
    public function testBuild(): void
    {
        $driver      = new Driver($this->getEntityManager());
        $typeManager = $driver->get(TypeManager::class);

        $objectType = $driver->get(Metadata::class)->get(Artist::class)->getGraphQLType();
        $connection = $typeManager->build(Connection::class, $objectType->name . '_Connection', $objectType);
        $this->assertEquals($objectType->name . '_Connection', $connection->name);
    }

    public function testBuildTwiceReturnsSameType(): void
    {
        $driver      = new Driver($this->getEntityManager());
        $typeManager = $driver->get(TypeManager::class);

        $objectType  = $driver->get(Metadata::class)->get(Artist::class)->getGraphQLType();
        $connection1 = $typeManager->build(Connection::class, $objectType->name . '_Connection', $objectType);
        $connection2 = $typeManager->build(Connection::class, $objectType->name . '_Connection', $objectType);

        $this->assertSame($connection1, $connection2);
    }
}
