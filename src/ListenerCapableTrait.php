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
 * ListenerCapableTrait
 *
 * Classes use this trait will be able to listen to events
 *
 * @package Phoole\Event
 */
trait ListenerCapableTrait
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @param  Provider $provider
     */
    public function setProvider(Provider $provider): void
    {
        $this->provider = $provider;
        $this->attachListeners();
    }

    /**
     * Returns array of ['classMethodName', int priority]
     *
     * e.g.
     * return [
     *     'methodOne',
     *     ['methodTwo', 50],
     * ];
     *
     * @return array
     */
    abstract protected function eventsListening(): array;

    /**
     * attach $this related listener callable to the provider
     */
    protected function attachListeners()
    {
        foreach ($this->eventsListening() as $method) {
            if (is_array($method)) {
                list($method, $priority) = $method;
                $this->provider->attach([$this, $method], $priority);
            } else {
                $this->provider->attach([$this, $method]);
            }
        }
    }
}