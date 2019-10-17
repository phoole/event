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

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * StoppableEvent prototype
 *
 * @package Phoole\Event
 */
abstract class StoppableEvent implements StoppableEventInterface
{
    /**
     * @var    bool
     */
    protected $stopped = false;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
