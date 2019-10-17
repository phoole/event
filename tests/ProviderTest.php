<?php

declare(strict_types=1);

namespace Phoole\Tests;

use Phoole\Event\Provider;
use PHPUnit\Framework\TestCase;
use Phoole\Event\StoppableEvent;

function myFunc(StoppableEvent $e)
{
    return $e;
}

class myClass
{
    public function __invoke(StoppableEvent $e)
    {
        echo "bingo";
        return $e;
    }

    public function myMethod(StoppableEvent $e)
    {
        return $e;
    }

    public static function myStatic(StoppableEvent $e)
    {
        return $e;
    }

    public function noParam()
    {

    }

    public function paramIsString(string $s)
    {
        return $s;
    }
}

class myEvent extends StoppableEvent
{
    public function stopIt(bool $stopped)
    {
        $this->stopped = $stopped;
    }
}

class ProviderTest extends TestCase
{
    private $obj;
    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new Provider();
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
     * @covers Phoole\Event\Provider::getCallableType()
     */
    public function testGetCallableType()
    {
        // string
        $this->assertEquals(
            'function',
            $this->invokeMethod('getCallableType', [__NAMESPACE__ . '\\myFunc'])
        );

        $this->assertEquals(
            'function',
            $this->invokeMethod('getCallableType', [function(StoppableEvent $e) { return $e; }])
        );

        // invokable
        $this->assertEquals(
            'invokable',
            $this->invokeMethod('getCallableType', [ new myClass() ])
        );

        // class
        $this->assertEquals(
            'class',
            $this->invokeMethod('getCallableType', [ [new myClass(), 'myMethod']])
        );

        $this->assertEquals(
            'class',
            $this->invokeMethod('getCallableType', [ [__NAMESPACE__ . '\\myClass', 'myStatic']])
        );

        // unknown ？
    }

    /**
     * @covers Phoole\Event\Provider::getParameterClass()
     */
    public function testGetParameterClass()
    {
        // string
        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [__NAMESPACE__ . '\\myFunc'])
        );

        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [function(StoppableEvent $e) { return $e; }])
        );

        // invokable
        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [ new myClass()])
        );

        // class
        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [[new myClass(), 'myMethod']])
        );

        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [[__NAMESPACE__ . '\\myClass', 'myStatic']])
        );

        // exception
        $this->expectExceptionMessage('must declare one object');
        $this->assertEquals(
            'Phoole\Event\StoppableEvent',
            $this->invokeMethod('getParameterClass', [[new myClass(), 'noParam']])
        );        
    }

    /**
     * @covers Phoole\Event\Provider::getParameterClass()
     */
    public function testGetParameterClass2()
    {
        $this->expectExceptionMessage('must declare one object');
        $this->assertEquals(
            'string',
            $this->invokeMethod('getParameterClass', [[new myClass(), 'paramIsString']])
        );
    }

    /**
     * @covers Phoole\Event\Provider::attach()
     * @covers Phoole\Event\Provider::getListenersForEvent()
     */
    public function testAttach()
    {
        // found
        $f1 = __NAMESPACE__ . '\\myFunc';
        $this->obj->attach($f1);
        $l = $this->obj->getListenersForEvent(new myEvent());
        $a = [];
        foreach ($l as $x) {
            $a[] = $x;
        }
        $this->assertEquals([$f1], $a);

        // priority matters
        $f2 = function(myEvent $e) {
            return $e;
        };
        $this->obj->attach($f2, 80);
        $l = $this->obj->getListenersForEvent(new myEvent());
        $a = [];
        foreach ($l as $x) {
            $a[] = $x;
        }
        $this->assertEquals([$f2, $f1], $a);

        // not found
        $l = $this->obj->getListenersForEvent(new myClass());
        $this->assertEquals(0, count($l));
    }
}