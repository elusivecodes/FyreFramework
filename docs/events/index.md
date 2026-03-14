# Events

Use events when you want your application or framework components to publish hooks that other code can react to.

The events section covers registering callbacks, grouping handlers into listener classes, and dispatching named or object events.

## Table of Contents

- [Start here](#start-here)
- [Events overview](#events-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on how you want to work with events:

- **Registering callbacks and dispatching events**: start with [Event Manager](event-manager.md)
- **Grouping related handlers into a class**: start with [Event Listeners](listeners.md)
- **Hooking into framework subsystems**: see pages such as [ORM Events](../orm/events.md) and [Queue Worker](../queue/worker.md)

## Events overview

Events are dispatched through [Event Manager](event-manager.md) and matched by an event identifier.

The main pieces are straightforward:

- `Fyre\Event\Event` is the standard named event object; its identifier comes from `Event::getName()`
- other event objects can also be dispatched; in that case the identifier is the event object's class name
- `EventManager` lets you register direct callbacks and dispatch events
- listener classes group handlers using `#[On]` and register them through `EventManager::addListener()`
- callbacks run in ascending priority order, with lower values executing first

## Pages in this section

- [Event Manager](event-manager.md) - register listeners and dispatch named or object events
- [Event Listeners](listeners.md) - group related handlers into listener classes with `#[On]`

## Related

- [ORM Events](../orm/events.md) - ORM lifecycle and model events
- [Queue Worker](../queue/worker.md) - job processing hooks and worker behavior
