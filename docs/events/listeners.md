# Event Listeners

Use listener classes when you want to group related event handlers and register them with one call.

Listener classes work with `EventManager`: you implement `EventListenerInterface`, mark methods with `#[On]`, and register the object with `addListener()`.

## Table of Contents

- [Start here](#start-here)
- [Declaring listener methods](#declaring-listener-methods)
  - [Listening to named events](#listening-to-named-events)
  - [Listening to object events](#listening-to-object-events)
  - [Listening to multiple events](#listening-to-multiple-events)
- [Registering a listener class](#registering-a-listener-class)
- [Discovery and caching](#discovery-and-caching)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual listener-class workflow is:

1. Implement `EventListenerInterface`.
2. Add `#[On]` to the methods that should handle events.
3. Register the listener instance with `EventManager::addListener()`.
4. Remove it later with `removeListener()` if needed.

```php
use Fyre\Event\Attributes\On;
use Fyre\Event\Event;
use Fyre\Event\EventListenerInterface;

final class AuditListener implements EventListenerInterface
{
    #[On('User.created')]
    public function onUserCreated(Event $event, string $id): void
    {
        // ...
    }
}
```

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

To listen to an object event, use the event class name in `#[On(...)]`.

For object events, the listener method receives the event object as the only argument.

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
use Fyre\Event\EventManager;

$eventManager = app(EventManager::class);
$listener = new AuditListener();

$eventManager->addListener($listener);
```

Remove the listener later by passing the same instance:

```php
$eventManager->removeListener($listener);
```

## Discovery and caching

When you call `addListener()`, the event manager discovers the methods marked with `#[On]` and registers them as callbacks.

That discovery is cached in memory per listener class. If a cache configuration exists under the key `_events`, the metadata is also stored through the cache layer.

Event metadata caching is optional. For example, you can configure a file-backed cache:

```php
use Fyre\Cache\Handlers\File\FileCacher;

return [
    'Cache' => [
        '_events' => [
            'className' => FileCacher::class,
            'path' => 'tmp/cache/events',
        ],
    ],
];
```

When listener attributes or method names change, clear the `_events` cache before new event managers are built. An existing `EventManager` keeps an in-memory copy; restart the process, or call `EventManager::clear()` and register its listeners again.

## Behavior notes

A few behaviors are worth keeping in mind:

- Public and protected methods can be discovered. Private handlers won’t be registered.
- If the `On` priority argument is omitted, the event manager uses `EventManager::PRIORITY_NORMAL`.
- For named `Event` dispatch, handler parameters must match what is actually passed: the `Event` instance first, then event data values only (keys are not passed).
- For object event dispatch, the handler receives only the event object.
- Clearing the `_events` cache does not update metadata already loaded by an existing `EventManager`.

## Related

- [Events](index.md) - overview and key concepts
- [Event Manager](event-manager.md) - register listeners and dispatch events
- [Cache](../cache/index.md) - configure the optional `_events` cache handler
