# Events

Use events when you want your application or framework components to publish hooks that other code can react to.

The events section covers registering callbacks, grouping handlers into listener classes, and dispatching named or object events.

## Table of Contents

- [Events overview](#events-overview)
- [Choose a listener style](#choose-a-listener-style)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Events overview

Events are dispatched through [Event Manager](event-manager.md) and matched by an event identifier.

- `Fyre\Event\Event` is the standard named event object; its identifier comes from `Event::getName()`
- other event objects can also be dispatched; in that case the identifier is the event object's class name
- `EventManager` lets you register direct callbacks and dispatch events
- listener classes group handlers using `#[On]` and register them through `EventManager::addListener()`
- `EventDispatcherTrait` gives application classes a small `dispatchEvent()` convenience API
- callbacks run in ascending priority order, with lower values executing first

## Choose a listener style

Use [Event Manager](event-manager.md) for direct callbacks, dispatching, and classes that publish their own events through `EventDispatcherTrait`. Use [Event Listeners](listeners.md) when several related handlers belong in one class and should be registered together through `#[On]` attributes.

Framework-specific event contracts remain with the feature that publishes them, such as [ORM Events](../orm/events.md) and [Queue Worker](../queue/worker.md).

## Pages in this section

- [Event Manager](event-manager.md) - register listeners and dispatch named or object events
- [Event Listeners](listeners.md) - group related handlers into listener classes with `#[On]`

## Related

- [ORM Events](../orm/events.md) - ORM lifecycle and model events
- [Queue Worker](../queue/worker.md) - job processing hooks and worker behavior
