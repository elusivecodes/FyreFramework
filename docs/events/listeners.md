# Event Listeners

Use listener classes when you want to group related event handlers and register them with one call.

Listener classes work with `EventManager`: you implement `EventListenerInterface`, mark methods with `#[On]`, and register the object with `addListener()`.

## Table of Contents

- [Start here](#start-here)
- [Listener contract](#listener-contract)
  - [Listening to named events](#listening-to-named-events)
  - [Listening to object events](#listening-to-object-events)
  - [Listening to multiple events](#listening-to-multiple-events)
- [Registering a listener class](#registering-a-listener-class)
- [Discovery and caching](#discovery-and-caching)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Define and register a listener class in four steps:

1. Implement `EventListenerInterface`.
2. Add `#[On]` to the methods that should handle events.
3. Register the listener instance with `EventManager::addListener()`.
4. Remove it later with `removeListener()` if needed.

The interface is a marker; the `#[On]` attributes define the actual event contract.

## Listener contract

| Event form | `#[On]` name | Method arguments |
| --- | --- | --- |
| named `Fyre\Event\Event` | the event name, such as `User.created` | the `Event` first, then each data value in order; data keys are discarded |
| another event object | the event class name | the event object only |

The optional second `#[On]` argument is the integer priority. Lower values run first; when it is omitted, `EventManager::PRIORITY_NORMAL` is used.

### Listening to named events

Use `#[On('Some.event')]` to listen to a named `Event`:

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

For an object event, use its class name in `#[On(...)]`:

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

`On` is repeatable, so one method can handle several events:

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

When you call `addListener()`, the event manager discovers public and protected methods marked with `#[On]` and registers them as callbacks. Private methods are not discovered.

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

When listener attributes or method names change, clear the `_events` cache before building new event managers. An existing manager keeps its in-memory metadata; restart the process, or call `clear()` and register its listeners again.

## Behavior notes

- `removeListener()` must receive the same listener instance that was passed to `addListener()` so its bound method callbacks can be matched.
- Listener method parameters must match the dispatch arguments in the contract table; the manager does not adapt payload names to parameter names.
- Clearing the `_events` cache does not update metadata already loaded by an existing `EventManager`.

## Related

- [Events](index.md) - overview and key concepts
- [Event Manager](event-manager.md) - register listeners and dispatch events
- [Cache](../cache/index.md) - configure the optional `_events` cache handler
