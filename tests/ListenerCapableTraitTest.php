<?php

declare(strict_types=1);

namespace Phoole\Tests;

use Phoole\Event\Provider;
use Phoole\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use Phoole\Event\ListenerCapableTrait;
use Phoole\Event\ListenerCapableInterface;

class MyListener implements ListenerCapableInterface
{
    use ListenerCapableTrait;

    public function methodOne(myEvent $event)
    {
        echo 'One';
        return $event;
    }

    public function methodTwo(myEvent $event)
    {
        echo 'Two';
        return $event;
    }

    public function eventsListening(): array
    {
        return [
            'methodOne',
            ['methodTwo', 80]
        ];
    }
}

class ListenerCapableTraitTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new MyListener();
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
     * @covers Phoole\Event\ListenerCapableTrait::setProvider()
     */
    public function testSetProvider()
    {
        $provider = new Provider();
        $dispatcher = new Dispatcher($provider);
        $this->obj->setProvider($provider);

        $this->expectOutputString('TwoOne');
        $dispatcher->dispatch(new myEvent());
    }
}