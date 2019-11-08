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

/**
 * ListenerCapableInterface
 *
 * Class implements this interface will be able to listen to events
 *
 * @package Phoole\Event
 */
interface ListenerCapableInterface
{
    /**
     * @param  Provider $provider
     */
    public function setProvider(Provider $provider): void;
}