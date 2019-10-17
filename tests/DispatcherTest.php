<?php

declare(strict_types=1);

namespace Phoole\Tests;

use Phoole\Event\Provider;
use Phoole\Event\Dispatcher;
use Phoole\Event\StoppableEvent;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    private $obj;
    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new Dispatcher();
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
     * @covers Phoole\Event\Dispatcher::addProvider()
     * @covers Phoole\Event\Dispatcher::getListeners()
     */
    public function testGetListeners()
    {
        $provider1 = new Provider();
        $f1 = function(StoppableEvent $e) { return 'called';};
        $provider1->attach($f1);
        $f2 = __NAMESPACE__ . '\\myFunc';
        $provider1->attach($f2, 100);
        $this->invokeMethod('addProvider', [ $provider1 ]);

        $provider2 = new Provider();
        $f3 = new myClass();
        $provider2->attach($f3);
        $f4 = [__NAMESPACE__ . '\\myClass', 'myStatic'];
        $provider2->attach($f4, 80);
        $this->invokeMethod('addProvider', [ $provider2 ]);

        $listeners = $this->invokeMethod('getListeners', [ new myEvent() ]);
        $result = [];
        foreach ($listeners as $l) {
            $result[] = $l;
        }
        $this->assertEquals([$f2, $f4, $f1, $f3], $result);
    }

    /**
     * @covers Phoole\Event\Dispatcher::__construct()
     */
    public function testConstruct()
    {
        $p1 = new Provider();
        $p2 = new Provider();
        $obj = new Dispatcher($p1, $p2);

        $f = new myClass();
        $p2->attach($f);

        $e = new myEvent();
        $this->expectOutputString('bingo');
        $obj->dispatch($e);
        $e->stopIt(true);
        $obj->dispatch($e);
    }

    /**
     * @covers Phoole\Event\Dispatcher::dispatch()
     */
    public function testDispatch()
    {
        $p1 = new Provider();
        $p2 = new Provider();
        $this->invokeMethod('addProvider', [ $p1 ]);
        $this->invokeMethod('addProvider', [ $p2 ]);

        $f = new myClass();
        $p2->attach($f);

        $this->expectOutputString('bingobingo');
        $e = new myEvent();
        $this->assertEquals($e, $this->obj->dispatch($e));
        $this->assertEquals($e, $this->obj->dispatch($e));
    }
}