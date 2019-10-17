<?php

/**
 * Phoole (PHP7.2+)
 *
 * @category  Library
 * @package   Phoole\Event
 * @copyright Copyright (c) 2019 Hong Zhang
 */
declare(strict_types=1);

namespace Phoole\Event;

use Phoole\Base\Queue\PriorityQueue;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Provider
 *
 * @package Phoole\Event
 */
class Provider implements ListenerProviderInterface
{
    /**
     * event classes listened
     * @var PriorityQueue[]
     */
    protected $listened = [];

    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $queue = new PriorityQueue();
        foreach ($this->listened as $class => $q) {
            if (is_a($event, $class) && count($q)) {
                $queue = $queue->combine($q);
            }
        }
        return $queue;
    }

    /**
     * Attach a listener (with default priority 50) to the provider
     *
     * @param  callable  $callable MUST be type-compatible with $event.
     * @param  int       $priority range 0 - 100, 0 means lower priority
     * @throws \InvalidArgumentException    unknown type of callable found
     * @throws \RuntimeException            reflection problem found
     * @return ListenerProviderInterface $this
     */
    public function attach(callable $callable, int $priority = 50): ListenerProviderInterface
    {
        $class = $this->getParameterClass($callable);
        if (!isset($this->listened[$class])) {
            $this->listened[$class] = new PriorityQueue();
        }
        $this->listened[$class]->insert($callable, $priority);
        return $this;
    }

    /**
     * Get callable argument class.
     * Borrowed from yiisoft/event-dispatcher
     *
     * @param  callable $callable
     * @throws \InvalidArgumentException    unknown type of callable found
     * @throws \RuntimeException            reflection problem found
     * @return string                       classname or interface name
     */
    protected function getParameterClass(callable $callable): string
    {
        try {
            $params = $this->getCallableParameters($callable);
            if (!isset($params[0]) || null === $params[0]->getType()) {
                throw new \InvalidArgumentException('Listener must declare one object as event');
            }

            $type = $params[0]->getType()->getName();
            if (class_exists($type) || interface_exists($type)) {
                return $type;
            }
            throw new \InvalidArgumentException('Listener must declare one object as event');
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Get callable parameters
     *
     * @param  callable $callable
     * @throws \InvalidArgumentException  unknown callable found
     * @throws \ReflectionException       reflection problem found
     * @return array
     */
    protected function getCallableParameters(callable $callable): array
    {
        switch ($this->getCallableType($callable)) {
            // class or object
            case 'class':
                $ref = new \ReflectionClass($callable[0]);
                $par = $ref->getMethod($callable[1])->getParameters();
                break;
            // function or closure
            case 'function':
                $ref = new \ReflectionFunction($callable);
                $par = $ref->getParameters();
                break;
            // invokable
            case 'invokable':
                $par = (new \ReflectionMethod($callable, '__invoke'))->getParameters();
                break;
            default:
                throw new \InvalidArgumentException('unknown type of callable');
        }
        return $par;
    }

    /**
     * Get callable parameters
     *
     * @param  callable $callable
     * @return string
     */
    protected function getCallableType(callable $callable): string
    {
        if (is_string($callable) || $callable instanceof \Closure) {
            return 'function';
        }

        if (is_array($callable)) {
            return 'class';
        }

        if (is_object($callable)) {
            return 'invokable';
        }

        return 'unknown';
    }
}
