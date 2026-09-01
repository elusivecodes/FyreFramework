# Event Manager

Use `Fyre\Event\EventManager` when you want to register callbacks and dispatch events across your application.

It supports direct callbacks, listener classes that use `#[On]`, the framework's named `Event` object, and arbitrary event objects.

## Table of Contents

- [Start here](#start-here)
- [Registering listeners](#registering-listeners)
  - [Register a callback](#register-a-callback)
  - [Register a listener class](#register-a-listener-class)
  - [Removing listeners](#removing-listeners)
- [Dispatching events](#dispatching-events)
  - [Dispatching a named `Event`](#dispatching-a-named-event)
  - [Dispatching an object event](#dispatching-an-object-event)
  - [Using `trigger()`](#using-trigger)
- [Working with `Event`](#working-with-event)
- [Dispatching from classes](#dispatching-from-classes)
- [Listener ordering](#listener-ordering)
- [Parent event managers](#parent-event-managers)
- [Manager operations](#manager-operations)
- [Related](#related)

## Start here

Resolve the event manager, register a callback, then trigger the event:

```php
use Fyre\Event\Event;
use Fyre\Event\EventManager;

$eventManager = app(EventManager::class);

$eventManager->on('User.created', static function(Event $event, string $id): void {
    // ...
});

$eventManager->trigger('User.created', '42');
```

## Registering listeners

Most examples on this page assume you already have an `$eventManager` instance.

### Register a callback

Use `on()` to register a callback for an event identifier:

```php
use Fyre\Event\Event;
use Fyre\Event\EventManager;

$eventManager->on(
    'User.created',
    static function(Event $event, string $id): void {
        // ...
    },
    EventManager::PRIORITY_HIGH
);
```

For object events, register the callback under the event class name:

```php
final class UserRegistered {}

$eventManager->on(
    UserRegistered::class,
    static function(UserRegistered $event): void {
        // ...
    }
);
```

### Register a listener class

If a class implements `EventListenerInterface`, `addListener()` registers the methods marked with `#[On]`.

```php
use Fyre\Event\Attributes\On;
use Fyre\Event\Event;
use Fyre\Event\EventListenerInterface;
use Fyre\Event\EventManager;

final class AuditListener implements EventListenerInterface
{
    #[On('User.created', EventManager::PRIORITY_NORMAL)]
    public function onUserCreated(Event $event, string $id): void
    {
        // ...
    }
}

$listener = new AuditListener();
$eventManager->addListener($listener);
```

For the fuller listener-class workflow, see [Event Listeners](listeners.md).

### Removing listeners

- Remove all callbacks for an event identifier with `off('Some.event')`.
- Remove a specific callback with `off('Some.event', $callback)`.
- Remove a listener class with `removeListener($listener)`.
- Clear all registered callbacks (and listener classes) with `clear()`.

## Dispatching events

`dispatch()` returns the same event object, so you can inspect changes made by listeners afterward.

`EventManager` implements PSR-14 `EventDispatcherInterface` and `ListenerProviderInterface`. The framework `Event` class implements `StoppableEventInterface`.

| Event object | Listener key | Callback arguments | Propagation stops when |
| --- | --- | --- | --- |
| `Fyre\Event\Event` | `Event::getName()` | the `Event`, followed by the values from `Event::getData()` | `stopPropagation()` is called or a listener sets the result to `false` |
| any other object | the object's exact class name | the event object only | the object implements `StoppableEventInterface` and reports that propagation is stopped |

### Dispatching a named `Event`

When dispatching `Event`, each callback is called with:

- the `Event` instance as the first argument, then
- each value from `Event::getData()` (keys are not passed as arguments).

To stop dispatching additional callbacks for an `Event`, call `Event::stopPropagation()` or set the result to `false` (via `Event::setResult(false)`).

```php
use Fyre\Event\Event;

$eventManager->on(
    'Mail.sent',
    static function(Event $event, string $messageId): void {
        // ...
    }
);

$event = new Event('Mail.sent', null, ['abc123']);
$eventManager->dispatch($event);
```

### Dispatching an object event

Most framework events use `Fyre\Event\Event`. You can also dispatch other event objects directly. For non-`Event` objects, each callback receives the event object as the only argument.

If the event implements PSR-14 `StoppableEventInterface`, dispatch stops before invoking the next listener when `isPropagationStopped()` returns `true`.

Example:

```php
final class UserRegistered {}

$event = new UserRegistered();

$eventManager->on(
    UserRegistered::class,
    static function(UserRegistered $event): void {
        // ...
    }
);

$eventManager->dispatch($event);
```

To make an object event stoppable, implement `StoppableEventInterface` and return `true` from `isPropagationStopped()` when you want dispatch to stop.

### Using `trigger()`

`trigger()` is a convenience for dispatching a named `Event`. It creates an event with a null subject, dispatches it, and returns the `Event` instance.

```php
use Fyre\Event\Event;

$eventManager->on(
    'Cache.miss',
    static function(Event $event, string $key): void {
        // ...
    }
);

$event = $eventManager->trigger('Cache.miss', 'users:42');
```

## Working with `Event`

Use `Event` when you need a named event with an optional subject, positional data, and a result listeners can inspect or change.

```php
use Fyre\Event\Event;

$event = new Event('User.created', $user, [$id]);
$eventManager->dispatch($event);

$subject = $event->getSubject();
$result = $event->getResult();
```

| Value | Methods | Dispatch behavior |
| --- | --- | --- |
| name | `getName()` | selects the registered listener group |
| subject | `getSubject()` | available to listeners through the event; not passed separately |
| data | `getData()`, `setData()` | values are passed after the event object; array keys are discarded |
| result | `getResult()`, `setResult()` | a result of `false` stops propagation after the current listener |
| propagation state | `isPropagationStopped()`, `stopPropagation()` | stopped events skip later listeners and the parent manager |

## Dispatching from classes

Use `EventDispatcherTrait` when a class needs to publish events through an injected `EventManager`. `dispatchEvent()` uses the current object as the subject unless you provide another object.

```php
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;

class Importer
{
    use EventDispatcherTrait;

    public function __construct(EventManager $eventManager)
    {
        $this->setEventManager($eventManager);
    }

    public function import(array $rows): void
    {
        // ...

        $this->dispatchEvent('Import.completed', [count($rows)]);
    }
}
```

Use `getEventManager()` and `setEventManager()` when the manager needs to be read or replaced directly.

## Listener ordering

Callbacks are executed in ascending priority order (lower values run first).

`EventManager` provides common priority constants:

- `EventManager::PRIORITY_HIGH` (10)
- `EventManager::PRIORITY_NORMAL` (100)
- `EventManager::PRIORITY_LOW` (200)

If `on()` is called without a priority, `PRIORITY_NORMAL` is used.

The sorted callback list is cached after first use. Calling `on()` or `off()` invalidates the cached order for that listener group.

## Parent event managers

Use a parent event manager when you want local listeners plus a shared higher-level listener set.

When a parent manager is configured:

- local callbacks are dispatched first
- the event is then dispatched to the parent manager, unless propagation has been stopped

This is useful when you want request-scoped or subsystem-specific listeners in a child manager, while still keeping shared process-wide listeners in a parent manager.

## Manager operations

| Task | Method | Notes |
| --- | --- | --- |
| Register a callback | `on($name, $callback, $priority = null)` | uses `PRIORITY_NORMAL` when priority is omitted |
| Remove callbacks | `off($name, $callback = null)` | removes the matching callback, or every callback for the name when none is supplied |
| Register an attributed listener | `addListener($listener)` | discovers its `#[On]` methods |
| Remove an attributed listener | `removeListener($listener)` | pass the same listener instance that was registered |
| Dispatch an object | `dispatch($event)` | returns the dispatched object |
| Trigger a named event | `trigger($name, ...$args)` | creates an `Event` with a null subject and `$args` as its data |
| Check a listener group | `has($name)` | checks only the current manager |
| Resolve callbacks | `getListenersForEvent($event)` | returns current-manager callbacks in priority order; parent callbacks are not included |
| Remove all callbacks | `clear()` | also clears discovered listener metadata and cached ordering held by this manager |

## Related

- [Events](index.md) - overview and key concepts
- [Event Listeners](listeners.md) - define listener classes with `#[On]`
- [Cache](../cache/index.md) - configure cache handlers used for listener metadata
- [ORM Events](../orm/events.md) - events published by the ORM layer
