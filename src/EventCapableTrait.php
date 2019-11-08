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

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * EventCapableTrait
 *
 * classes use this trait will be able to trigger events
 *
 * @package   Phoole\Event
 * @interface EventCapableInterface
 */
trait EventCapableTrait
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * set the dispatcher
     *
     * @param  EventDispatcherInterface $dispatcher
     * @return void
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Using the dispatcher to trigger an event. returns the event object
     *
     * @param  object $event
     * @return object
     */
    protected function triggerEvent(object $event): object
    {
        return $this->dispatcher->dispatch($event);
    }
}