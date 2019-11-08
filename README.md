# event
[![Build Status](https://travis-ci.com/phoole/event.svg?branch=master)](https://travis-ci.com/phoole/event)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phoole/event/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phoole/event/?branch=master)
[![Code Climate](https://codeclimate.com/github/phoole/event/badges/gpa.svg)](https://codeclimate.com/github/phoole/event)
[![PHP 7](https://img.shields.io/packagist/php-v/phoole/event)](https://packagist.org/packages/phoole/event)
[![Latest Stable Version](https://img.shields.io/github/v/release/phoole/event)](https://packagist.org/packages/phoole/event)
[![License](https://img.shields.io/github/license/phoole/event)]()

A slim and powerful PSR-14 event manager library for PHP

Installation
---
Install via the `composer` utility.

```
composer require "phoole/event"
```

or add the following lines to your `composer.json`

```json
{
    "require": {
       "phoole/event": "1.*"
    }
}
```

Features
---

- Full PSR-14 support

- Support for classes want event-triggering capability with `EventCapableTrait` 

- Support for classes want event-listening capability with `ListenerCapableTrait`

Usage
---

- <a name="start"></a>Quick start

  ```php
  use Phoole\Event\Provider;
  use Phoole\Event\Dispatcher;
  use Phoole\Event\Events\StoppableEvent;
  
  // create your own event class
  class MyEvent extends StoppableEvent {
  }
  
  // an invokable class for event handling
  class MyEventHandler
  {
      public function __invoke(MyEvent $event)
      {
           echo 'handling event...';
           return $event; // optional
      }
  } 
  
  // initiate event dispatcher
  $events = new Dispatcher(new Provider());

  // bind a callable with default priority 50 (0-100)
  $provider->attach(function(myEvent $event) {
      echo 'triggered...';
      return $event; // optional
  });
  
  // bind a invokable object with priority 80
  $provider->attach(new MyEventHandler(), 80);

  // fire the trigger, wil see output 'handling event...triggered...'
  $events->dispatch(new MyEvent());
  ```

- <a name="hierarchy"></a>Event hierarchy

  Instead of using event name to trigger like many event libraries were doing.
  PSR-14 compliant event libraries now only support triggering with event object. 
  By setting up a meaningful event object hierarchy, developers may achieve great
  flexibility in event handling.
  
  Listeners will **ONLY** handle the events (and their subclasses) configured 
  with the event parameter. 
  
  ```php
  use Phoole\Event\Events\StoppableEvent;
  
  // my own event hierarchy top
  class MyEvent extends StoppableEvent
  {
  }
  
  // my user authentication event
  class MyAuthEvent extends MyEvent
  {
      protected $userInfo;
  
      public function __construct(array $userInfo)
      {
          $this->userInfo = $userInfo;
      }
  
      public function getUserInfo(): array
      {
          return $this->userInfo;
      }
  }
  
  $provider = new Provider();
  $events = new Dispatcher($provider);
  
  // attach a listener to the event hierarchy top
  $provider->attach(function(MyEvent $event) {
      echo 'Event '.get_class($event).' fired';
      return $event; // optional
  });
  
  // attach a listener for logging users
  $provider->attach(function(MyAuthEvent $event)) use ($logger) {
      $logger->info(sprintf("User '%s' logged", $event->getUserInfo()['name']));
      return $event; // optional
  });
  
  ...

  // upon user logged in, trigger the event
  // BOTH listeners will process THIS event
  $events->dispatcher(new MyAuthEvent($userInfo));
  ```
  
- <a name="listener"></a>Create a **listener class**

  A listener class may implement the `ListenerCapableInterface` and use
  `ListenerCapableTrait`.

  ```php
  use Phoole\Event\ListenerCapableTrait;
  use Phoole\Event\ListenerCapableInterface;

  // my listener class
  class MyListener implements ListenerCapableInterface
  {
      use ListenerCapableTrait;
  
      // define on listener method
      public function MethodOne(MyEvent $event)
      {
           echo 'handling MyEvent in MethodOne...';
      }
      
      // define another listener method
      public function MethodTwo(MyEvent $event)
      {
          echo 'handling MyEvent in MethodTwo...';
      }
  
      // config this listener
      protected function eventsListening()
      {
          return [
              'MethodOne',
              ['MethodTwo', 80] // with priority 80
          ];
      }
  }
  
  // global dispatcher & provider
  $provider = new Provider();
  $events = new Dispatcher($provider);
  
  // set provider is enough
  $listener = new MyListener();
  $listener->setProvider($provider);
  
  // fire the event
  $events->dispatch(new MyEvent());
  ```
  
  Along with container library `phoole/di`, developer may not event worry
  about setting up dispatcher, provider or injecting the provider.
  
  ```php
  use Phoole\Di\Container;
  
  // initiate the listener with dependencies injected
  $listener = Container::create(MyListener::class);
  
  // fire the event
  Container::events()->dispatch(new MyEvent());
  ```

- <a name="eventcapable"></a>Triggering events in your classes

  `EventCapableInterface` and `EventCapableTrait` give developers the ability
  of triggering events in their classes.

  ```php
  use Phoole\Event\EventCapableTrait;
  use Phoole\Event\EventCapableInterface;
  
  class MyEventCapable implements EventCapableInterface
  {
      use EventCapableTrait;
  
      public function myMethod()
      {
           $this->triggerEvent(new MyEvent());
      }
  }
  
  // global dispatcher & provider
  $provider = new Provider();
  $events = new Dispatcher($provider);
  
  $eventCapable = new MyEventCapable();
  $eventCapable->setDispatcher($dispatcher);
  
  // will trigger an event
  $eventCapable->myMethod();
  ```
  
  Along with container library `phoole/di`, developer may not event worry
  about setting up dispatcher, provider or injecting the dispatcher.
  
  ```php
  // initiate object with dependencies injected
  $eventCapable = Container::create(MyEventCapable::class);
  
  // will trigger an event
  $eventCapable->myMethod();
  ```

Testing
---

```bash
$ composer test
```

Dependencies
---

- PHP >= 7.2.0

- Phoole/base 1.*

License
---

- [Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0)