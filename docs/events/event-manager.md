# Event Manager

`Fyre\Event\EventManager` registers callbacks (and attribute-based listener classes) and dispatches events (as a PSR-14 dispatcher and listener provider). It supports both named `Event` events and arbitrary event objects matched by class name.

## Table of Contents

- [Purpose](#purpose)
- [Core idea](#core-idea)
- [Registering listeners](#registering-listeners)
  - [Register a callback](#register-a-callback)
  - [Register an attribute-based listener](#register-an-attribute-based-listener)
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

## Purpose

Use `EventManager` to publish and react to framework behavior without tightly coupling your code to the implementation details of the component raising the event.

## Core idea

Think of the event manager as a map from **event identifier → ordered list of callbacks**:

- For `Event`, the identifier is the string returned by `Event::getName()`.
- For other events, the identifier is the event object’s class name (for example, `UserRegistered::class`).

`dispatch()` resolves callbacks via `getListenersForEvent()` and then executes them in order.

Terminology used in this guide:

- A **callback** is an individual callable registered with `on()`.
- A **listener class** is an object that implements `EventListenerInterface` and is registered with `addListener()` (its annotated methods are registered as callbacks).

## Registering listeners

Most examples on this page assume you already have an `$eventManager` instance (for example via dependency injection).

You can also resolve it from the container (see [Helpers](../core/helpers.md)) via `$eventManager = app(Fyre\Event\EventManager::class);`.

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

When dispatching an object event, register the callback under the class name:

```php
final class UserRegistered {}

$eventManager->on(
    UserRegistered::class,
    static function (UserRegistered $event): void {
        // ...
    }
);
```

### Register an attribute-based listener

If a class implements `EventListenerInterface`, `addListener()` discovers public methods annotated with `#[On]` and registers them under the attribute’s event name and priority.

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

For the full attribute-based listener workflow (including discovery caching), see [Event Listeners](listeners.md).

### Removing listeners

- Remove all callbacks for an event identifier with `off('Some.event')`.
- Remove a specific callback with `off('Some.event', $callback)`.
- Remove a listener class with `removeListener($listener)`.
- Clear all registered callbacks (and listener classes) with `clear()`.

## Dispatching events

Note: `dispatch()` returns the event object, so you can read any changes made by callbacks (PSR-14 semantics).

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

For non-`Event` objects, each callback receives the event object as the only argument.

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

`trigger()` is a convenience for dispatching a named `Event`: it creates a new `Event($name, null, $args)`, dispatches it, and returns the `Event` instance. The `...$args` values become the event data (and are passed to callbacks as positional values).

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

An event manager may be constructed with a parent manager. When present:

- local callbacks are dispatched first
- the event is then dispatched to the parent manager, unless propagation has been stopped
- for `Event`, propagation is checked via `Event::isPropagationStopped()`
- for other events, the parent dispatch is skipped only when the event implements `StoppableEventInterface` and reports `isPropagationStopped() === true`

This enables layering callbacks (for example, request-scoped callbacks in a child manager with process-wide callbacks in a parent manager) while keeping ordering predictable within each manager.

## Method guide

This section is a quick reference to the methods you’ll use most when registering callbacks (and listener classes) and dispatching events.

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

Discover `#[On]` attributes on a listener object and register its annotated public methods as callbacks.

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

Dispatch an event object to all registered callbacks for that event.

Arguments:
- `$event` (`object`): the event to dispatch.

```php
use Fyre\Event\Event;

$eventManager->dispatch(new Event('Mail.sent'));
```

#### **Trigger a named event** (`trigger()`)

Create and dispatch a named `Event` with a null subject, returning the `Event` instance.

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

#### **Resolve callbacks for an event** (`getListenersForEvent()`)

Return the list of callbacks that would be invoked for a given event by the current manager (in dispatch order).

If a parent event manager is configured, parent listeners are dispatched separately by `dispatch()`.

Arguments:
- `$event` (`object`): the event instance to resolve callbacks for.

```php
use Fyre\Event\Event;

$callbacks = $eventManager->getListenersForEvent(new Event('Mail.sent'));
```

#### **Access the discovery cache** (`getCache()`)

Return the configured `Cacher` used to cache attribute listener discovery, or `null` when no `_events` cache is configured.

```php
$cache = $eventManager->getCache();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Listener ordering is by ascending priority (lower values run first).
- For `Event`, callbacks receive the `Event` instance first, then the event data values only (keys are not passed).
- If the event implements `StoppableEventInterface` (including `Event`), dispatch stops before the next listener when propagation has been stopped, and parent dispatch is skipped.
- For `Event`, if a listener sets the result to `false`, the event manager calls `Event::stopPropagation()` after that listener runs.

## Related

- [Events](index.md) — overview and key concepts.
- [Event Listeners](listeners.md) — defining listener classes with `#[On]`.
- [Cache](../cache/index.md) — configuring the `_events` cache.
- [ORM Events](../orm/events.md) — events published by the ORM layer.
