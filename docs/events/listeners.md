# Event Listeners

Event callbacks run in response to events dispatched through the event manager. In addition to registering individual callbacks, you can group handlers into a listener class by implementing `EventListenerInterface` and marking public methods with the `#[On]` attribute.

For event registration and dispatch basics, see [Event Manager](event-manager.md).

## Table of Contents

- [Purpose](#purpose)
- [How attribute listeners work](#how-attribute-listeners-work)
- [Declaring listener methods](#declaring-listener-methods)
  - [Listening to named events](#listening-to-named-events)
  - [Listening to object events](#listening-to-object-events)
- [Listening to multiple events](#listening-to-multiple-events)
- [Registering a listener class](#registering-a-listener-class)
- [Listener discovery and caching](#listener-discovery-and-caching)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use attribute-based listeners when you want to group related event handlers into a single class and register them with one call to `EventManager::addListener()`.

Terminology used in this guide:

- A **callback** is an individual callable registered with `EventManager::on()`.
- A **listener class** is an object that implements `EventListenerInterface` and is registered with `EventManager::addListener()`.

Most examples on this page assume you already have an `$eventManager` instance (for example via dependency injection).

If helpers are loaded, you can also resolve it from the container (see [Helpers](../core/helpers.md)) via `$eventManager = app(Fyre\Event\EventManager::class);`.

## How attribute listeners work

Attribute-based listeners are a convenient way to group related event handlers into a single class.

To opt in:

- implement `EventListenerInterface` (a marker interface), and
- annotate public methods with `#[On]`.

When you call `EventManager::addListener()`, the event manager reflects on the listener class, discovers `#[On]` attributes, and registers each annotated method as a callback under the attribute’s event name and priority.

## Declaring listener methods

### Listening to named events

Use `#[On('Some.event')]` to listen to a named `Event`. The method receives the `Event` instance first, followed by each value from the event’s data (keys are not passed as arguments).

See [Dispatching a named `Event`](event-manager.md#dispatching-a-named-event) for how listener arguments are passed.

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
```

### Listening to object events

`On` stores the event name as a string. To listen to arbitrary event objects, register the listener under the object’s class name (for example, `SomeEvent::class`).

For object events, the callback is invoked with the event object as the only argument.

See [Dispatching an object event](event-manager.md#dispatching-an-object-event) for how object events are dispatched and stopped.

```php
use Fyre\Event\Attributes\On;
use Fyre\Event\EventListenerInterface;
use Fyre\Event\EventManager;

final class UserRegistered {}

final class RegistrationListener implements EventListenerInterface
{
    #[On(UserRegistered::class, EventManager::PRIORITY_HIGH)]
    public function onUserRegistered(UserRegistered $event): void
    {
        // ...
    }
}
```

### Listening to multiple events

`On` is repeatable, so you can attach it more than once (either on the same method, or across multiple methods).

```php
use Fyre\Event\Attributes\On;
use Fyre\Event\Event;
use Fyre\Event\EventListenerInterface;
use Fyre\Event\EventManager;

final class MetricsListener implements EventListenerInterface
{
    #[On('User.created', EventManager::PRIORITY_LOW)]
    #[On('User.deleted', EventManager::PRIORITY_LOW)]
    public function onUserChanged(Event $event, string $id): void
    {
        // ...
    }
}
```

## Registering a listener class

Register the listener instance with the event manager:

```php
$listener = new AuditListener();

$eventManager->addListener($listener);
```

Remove the listener later by passing the same instance:

```php
$eventManager->removeListener($listener);
```

## Listener discovery and caching

Attribute discovery happens via reflection and is cached per listener class.

When you register or remove a listener with `addListener()` / `removeListener()`:

- If a parent event manager is configured, discovery and caching are delegated to the parent manager.
- Otherwise, the event manager caches discovered metadata in-memory by `$listener::class`.
- If a cache configuration exists under the key `_events`, it is used to remember the discovered metadata (keyed by the listener class name, with namespace separators replaced by `.`).

Discovery scans all public methods and reads attributes using an `instanceof` match for `On`, so attributes that extend `On` are also discovered.

## Behavior notes

A few behaviors are worth keeping in mind:

- Only **public** methods are discovered. Private/protected handlers won’t be registered.
- If `On` is constructed with a `null` priority, the event manager treats it as `EventManager::PRIORITY_NORMAL`.
- For named `Event` dispatch, handler parameters must match what is actually passed: the `Event` instance first, then event data values only (keys are not passed).
- For object event dispatch, the handler receives **only** the event object.
- Discovery caching is per listener class; if you change attributes or method names while using the `_events` cache, clear the cache so the updated metadata is discovered.

## Related

- [Events](index.md) — overview and key concepts.
- [Event Manager](event-manager.md) — registering listeners and dispatching events.
