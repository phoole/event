<?php

declare(strict_types=1);

namespace Phoole\Tests;

use Phoole\Event\Provider;
use Phoole\Event\Dispatcher;
use Phoole\Event\StoppableEvent;
use PHPUnit\Framework\TestCase;

class StoppableEventTest extends TestCase
{
    private $obj;
    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new myEvent();
        $this->ref = new \ReflectionClass(get_class($this->obj));
    }

    protected function tearDown(): void
    {
        $this->obj = $this->ref = null;
        parent::tearDown();
    }

    protected function invokeMethod($methodName, array $parameters = array())
    {
        $method = $this->ref->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->obj, $parameters);
    }

    /**
     * @covers Phoole\Event\StoppableEvent::isPropagationStopped()
     */
    public function testIsPropagationStopped()
    {
        $this->obj->stopIt(true);
        $this->assertTrue($this->obj->isPropagationStopped());

        $this->obj->stopIt(false);
        $this->assertFalse($this->obj->isPropagationStopped());
    }
}