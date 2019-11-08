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
use Phoole\Base\Reflect\ParameterTrait;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Provider
 *
 * @package Phoole\Event
 */
class Provider implements ListenerProviderInterface
{
    use ParameterTrait;

    /**
     * event classes listened
     *
     * @var PriorityQueue[]
     */
    protected $listened = [];

    /**
     * @param  object $event
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
     * @param  callable $callable  MUST be type-compatible with $event.
     * @param  int      $priority  range 0 - 100, 0 means lower priority
     * @return ListenerProviderInterface $this
     * @throws \RuntimeException            reflection problem found
     * @throws \InvalidArgumentException    unknown type of callable found
     */
    public function attach(callable $callable, int $priority = 50): ListenerProviderInterface
    {
        $class = $this->getEventClass($callable);
        if (!isset($this->listened[$class])) {
            $this->listened[$class] = new PriorityQueue();
        }
        $this->listened[$class]->insert($callable, $priority);
        return $this;
    }

    /**
     * Get callable's first argument EVENT class.
     *
     * @param  callable $callable
     * @return string                       classname or interface name
     * @throws \RuntimeException            reflection problem found
     * @throws \InvalidArgumentException    unknown type of callable found
     */
    protected function getEventClass(callable $callable): string
    {
        try {
            $params = $this->getCallableParameters($callable);

            $error = 'Listener must declare one object as event';
            if (1 != count($params)) {
                throw new \InvalidArgumentException($error);
            }

            $type = $params[0]->getType()->getName();
            if (class_exists($type) || interface_exists($type)) {
                return $type;
            }
            throw new \InvalidArgumentException($error);
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}