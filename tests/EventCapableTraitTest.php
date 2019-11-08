<?php

declare(strict_types=1);

namespace Phoole\Tests;

use Phoole\Event\Provider;
use Phoole\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use Phoole\Event\EventCapableTrait;
use Phoole\Event\EventCapableInterface;

class MyEventCapable implements EventCapableInterface
{
    use EventCapableTrait;
}

class EventCapableTraitTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new MyEventCapable();
        $this->ref = new \ReflectionClass(get_class($this->obj));
    }

    protected function tearDown(): void
    {
        $this->obj = $this->ref = NULL;
        parent::tearDown();
    }

    protected function invokeMethod($methodName, array $parameters = array())
    {
        $method = $this->ref->getMethod($methodName);
        $method->setAccessible(TRUE);
        return $method->invokeArgs($this->obj, $parameters);
    }

    protected function getPrivateProperty($obj, $propertyName)
    {
        $ref = new \ReflectionClass(get_class($obj));
        $property = $ref->getProperty($propertyName);
        $property->setAccessible(TRUE);
        return $property->getValue($obj);
    }

    /**
     * @covers Phoole\Event\EventCapableTrait::setDispatcher()
     */
    public function testSetDispatcher()
    {
        $dispatcher = new Dispatcher();
        $this->obj->setDispatcher($dispatcher);
        $this->assertTrue(
            $dispatcher === $this->getPrivateProperty($this->obj, 'dispatcher')
        );
    }

    /**
     * @covers Phoole\Event\EventCapableTrait::triggerEvent()
     */
    public function testTriggerEvent()
    {
        $provider = new Provider();
        $dispatcher = new Dispatcher($provider);
        $this->obj->setDispatcher($dispatcher);
        $provider->attach(new myClass());

        $this->expectOutputString('bingo');
        $this->invokeMethod('triggerEvent', [new myEvent()]);
    }
}