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
 * EventCapableInterface
 *
 * Class implements this interface will be able to trigger events
 *
 * @package Phoole\Event
 */
interface EventCapableInterface
{
    /**
     * set the event dispatcher
     *
     * @param  EventDispatcherInterface $dispatcher
     * @return void
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher): void;
}