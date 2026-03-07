# Events

Events let framework components publish hooks that your application can listen to, so you can react to and extend behavior without tightly coupling components.

## Table of Contents

- [Events overview](#events-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Events overview

Events are dispatched through `EventManager` (see [Event Manager](event-manager.md)) and matched by an **event identifier**. You can register individual callbacks or register listener classes that contribute callbacks via `#[On]`.

- Named events use `Event` and are identified by `Event::getName()`.
- Object events use the dispatched object’s class name as the identifier (for example, `UserRegistered::class`).
- Callbacks are executed in ascending priority order (lower values run first).

## Pages in this section

- [Event Manager](event-manager.md) — registering listeners and dispatching events.
- [Event Listeners](listeners.md) — defining listener classes with `#[On]`.

## Related

- [ORM Events](../orm/events.md) — ORM lifecycle and model events.
- [Queue Worker](../queue/worker.md) — job processing hooks and worker behavior.
