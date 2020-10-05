<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL\GraphQL;

use ApiSkeletonsTest\Doctrine\GraphQL\AbstractTest;
use ApiSkeletons\Doctrine\GraphQL\Context;

class ContextTest extends AbstractTest
{
    public function testContextObjectDefaults()
    {
        $context = new Context();

        $this->assertEquals(1000, $context->getLimit());
        $this->assertEquals('default', $context->getHydratorSection());
        $this->assertEquals(false, $context->getUseHydratorCache());
        $this->assertEquals(false, $context->getUsePartials());
    }

    public function testContextObjectCustom()
    {
        $context = new Context();
        $context->setHydratorSection('test');
        $context->setUseHydratorCache(true);
        $context->setLimit(2000);
        $context->setUsePartials(true);

        $this->assertEquals(2000, $context->getLimit());
        $this->assertEquals('test', $context->getHydratorSection());
        $this->assertEquals(true, $context->getUseHydratorCache());
        $this->assertEquals(true, $context->getUsePartials());
    }
}
