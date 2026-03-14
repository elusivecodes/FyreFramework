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
- [Listener ordering](#listener-ordering)
- [Parent event managers](#parent-event-managers)
- [Method guide](#method-guide)
  - [Listener registration](#listener-registration)
  - [Dispatching](#dispatching)
  - [Utilities](#utilities)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual event workflow is:

1. Get an `EventManager` instance.
2. Register a callback with `on()` or a listener class with `addListener()`.
3. Dispatch a named `Event` or another event object.
4. Stop propagation when you need later listeners to be skipped.

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
    static function (Event $event, string $id): void {
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
    static function (UserRegistered $event): void {
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

`dispatch()` returns the event object, so you can inspect any changes made by listeners afterward.

### Dispatching a named `Event`

When dispatching `Event`, each callback is called with:

- the `Event` instance as the first argument, then
- each value from `Event::getData()` (keys are not passed as arguments).

To stop dispatching additional callbacks for an `Event`, call `Event::stopPropagation()` or set the result to `false` (via `Event::setResult(false)`).

```php
use Fyre\Event\Event;

$eventManager->on(
    'Mail.sent',
    static function (Event $event, string $messageId): void {
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
    static function (UserRegistered $event): void {
        // ...
    }
);

$eventManager->dispatch($event);
```

To make an object event stoppable, implement `StoppableEventInterface` and return `true` from `isPropagationStopped()` when you want dispatch to stop.

### Using `trigger()`

`trigger()` is a convenience for dispatching a named `Event`. It creates a new `Event`, dispatches it, and returns the `Event` instance.

```php
use Fyre\Event\Event;

$eventManager->on(
    'Cache.miss',
    static function (Event $event, string $key): void {
        // ...
    }
);

$event = $eventManager->trigger('Cache.miss', 'users:42');
```

## Listener ordering

Callbacks are executed in ascending priority order (lower values run first).

`EventManager` provides common priority constants:

- `EventManager::PRIORITY_HIGH` (10)
- `EventManager::PRIORITY_NORMAL` (100)
- `EventManager::PRIORITY_LOW` (200)

If `on()` is called without a priority, `PRIORITY_NORMAL` is used.

## Parent event managers

Use a parent event manager when you want local listeners plus a shared higher-level listener set.

When a parent manager is configured:

- local callbacks are dispatched first
- the event is then dispatched to the parent manager, unless propagation has been stopped

This is useful when you want request-scoped or subsystem-specific listeners in a child manager, while still keeping shared process-wide listeners in a parent manager.

## Method guide

This section covers the methods you are most likely to use directly.

### Listener registration

#### **Register a callback** (`on()`)

Register a callback for an event identifier (a named event string or an event object class name).

Arguments:
- `$name` (`string`): the event identifier.
- `$callback` (`callable`): the callback to register.
- `$priority` (`int|null`): the callback priority (lower values run first).

```php
use Fyre\Event\Event;
use Fyre\Event\EventManager;

$eventManager->on(
    'User.created',
    static function (Event $event, string $id): void {
        // ...
    },
    EventManager::PRIORITY_HIGH
);
```

#### **Remove callbacks** (`off()`)

Remove callbacks for an event identifier, optionally removing only a single callback.

Arguments:
- `$name` (`string`): the event identifier.
- `$callback` (`callable|null`): the callback to remove, or `null` to remove all callbacks for the identifier.

```php
$eventManager->off('User.created');
```

#### **Register a listener class** (`addListener()`)

Register a listener object that uses `#[On]` attributes.

Arguments:
- `$listener` (`EventListenerInterface`): the listener object to register.

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

#### **Remove a listener class** (`removeListener()`)

Remove callbacks previously registered via `addListener()`. Pass the same listener instance that was registered.

Arguments:
- `$listener` (`EventListenerInterface`): the listener object to unregister.

```php
$eventManager->removeListener($listener);
```

### Dispatching

#### **Dispatch an event** (`dispatch()`)

Dispatch an event object to all matching listeners.

Arguments:
- `$event` (`object`): the event to dispatch.

```php
use Fyre\Event\Event;

$eventManager->dispatch(new Event('Mail.sent'));
```

#### **Trigger a named event** (`trigger()`)

Create and dispatch a named `Event` with a null subject.

Arguments:
- `$name` (`string`): the event name.
- `...$args` (`mixed`): the event data values.

```php
$event = $eventManager->trigger('Cache.miss', 'users:42');
```

### Utilities

#### **Check whether callbacks exist** (`has()`)

Check whether any callbacks are registered for an event identifier on the current manager.

Arguments:
- `$name` (`string`): the event identifier.

```php
if ($eventManager->has('User.created')) {
    // ...
}
```

#### **Clear all callbacks** (`clear()`)

Remove all registered callbacks (including those registered via listener classes) and any cached ordering.

```php
$eventManager->clear();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Listener ordering is by ascending priority (lower values run first).
- For `Event`, callbacks receive the `Event` instance first, then the event data values only (keys are not passed).
- For object events, listeners receive only the event object.
- If the event implements `StoppableEventInterface` (including `Event`), dispatch stops before the next listener when propagation has been stopped, and parent dispatch is skipped.
- For `Event`, if a listener sets the result to `false`, the event manager calls `Event::stopPropagation()` after that listener runs.

## Related

- [Events](index.md) - overview and key concepts
- [Event Listeners](listeners.md) - define listener classes with `#[On]`
- [Cache](../cache/index.md) - configure the `_events` cache
- [ORM Events](../orm/events.md) - events published by the ORM layer
