<?php

declare(strict_types=1);

namespace ApiSkeletonsTest\Doctrine\GraphQL\Feature\Event;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\BuildMetadata;
use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ArrayObject;
use League\Event\EventDispatcher;

/**
 * This test uses both EventDefinition and QueryBuidlerTest to add a new
 * field to an entity type and filter it.
 */
class BuildMetadataTest extends AbstractTest
{
    public function testEvent(): void
    {
        $test = $this;

        $driver = new Driver($this->getEntityManager());

        $driver->get(EventDispatcher::class)->subscribeTo(
            'metadata.build',
            static function (BuildMetadata $event) use ($test): void {
                $metadata = $event->getMetadata();

                $test->assertEquals('metadata.build', $event->eventName());
                $test->assertInstanceOf(ArrayObject::class, $event->getMetadata());
                $test->assertEquals(0, $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit']);

                $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit'] = 100;
            },
        );

        $metadata = $driver->get('metadata');

        $this->assertInstanceOf(ArrayObject::class, $metadata);
        $test->assertEquals(100, $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit']);
    }

    public function testEventWithGlobalEnable(): void
    {
        $test = $this;

        $driver = new Driver($this->getEntityManager(), new Config(['globalEnable' => true]));

        $driver->get(EventDispatcher::class)->subscribeTo(
            'metadata.build',
            static function (BuildMetadata $event) use ($test): void {
                $metadata = $event->getMetadata();

                $test->assertEquals('metadata.build', $event->eventName());
                $test->assertInstanceOf(ArrayObject::class, $event->getMetadata());
                $test->assertEquals(0, $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit']);

                $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit'] = 100;
            },
        );

        $metadata = $driver->get('metadata');

        $this->assertInstanceOf(ArrayObject::class, $metadata);
        $test->assertEquals(100, $metadata['ApiSkeletonsTest\Doctrine\GraphQL\Entity\Performance']['limit']);
    }
}
