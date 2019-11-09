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

use Phoole\Base\Queue\UniquePriorityQueue;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatcher
 *
 * @package Phoole\Event
 */
class Dispatcher implements EventDispatcherInterface
{
    /**
     * @var Provider[]
     */
    protected $providers = [];

    /**
     * Dispatcher constructor.
     *
     * @param  Provider ...$providers
     */
    public function __construct(Provider ...$providers)
    {
        foreach ($providers as $p) {
            $this->addProvider($p);
        }
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param  object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event)
    {
        foreach ($this->getListeners($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }
        return $event;
    }

    /**
     * Add a provider to the dispatcher
     *
     * @param  Provider $provider
     * @return  void
     * @throws  \RuntimeException  if provider duplicated
     */
    protected function addProvider(Provider $provider)
    {
        $hash = \spl_object_hash($provider);
        if (!isset($this->providers[$hash])) {
            $this->providers[$hash] = $provider;
        } else {
            throw new \RuntimeException("Provider duplicated");
        }
    }

    /**
     * @param  object $event
     * @return iterable
     */
    protected function getListeners(object $event): iterable
    {
        $queue = new UniquePriorityQueue();
        foreach ($this->providers as $provider) {
            /** @var UniquePriorityQueue $q */
            $q = $provider->getListenersForEvent($event);
            if (count($q)) {
                $queue = $queue->combine($q);
            }
        }
        return $queue;
    }
}