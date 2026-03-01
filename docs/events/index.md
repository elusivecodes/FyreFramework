# Events

🧭 Events let framework components publish hooks that your application can listen to, so you can react to and extend behavior without tightly coupling components.

## Table of Contents

- [Start here](#start-here)
- [Events overview](#events-overview)
- [Pages in this section](#pages-in-this-section)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- Start with [Event Manager](event-manager.md) to register listeners (`on()` / `off()`) and dispatch events (`dispatch()` / `trigger()`).
- See [Event Listeners](listeners.md) to organize handlers into classes (`#[On]`) and use listener discovery.

## Events overview

🧩 Events are dispatched through `EventManager` (see [Event Manager](event-manager.md)) and matched by an **event identifier**. Listeners can be registered as individual callbacks or as attribute-based listener classes.

- Named events use `Event` and are identified by `Event::getName()`.
- Object events use the dispatched object’s class name as the identifier (for example, `UserRegistered::class`).
- Listener callbacks are executed in ascending priority order (lower values run first).

## Pages in this section

- [Event Manager](event-manager.md) — registering listeners and dispatching events.
- [Event Listeners](listeners.md) — defining listener classes with `#[On]`.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- For `Event`, listeners receive the `Event` instance first, then the event data values only (keys are not passed).
- If an `Event` result is set to `false`, propagation is stopped automatically.
- If an event manager has a parent manager, the parent is dispatched after local listeners unless propagation has been stopped.

## Related

- [ORM Events](../orm/events.md) — ORM lifecycle and model events.
- [Queue Worker](../queue/worker.md) — job processing hooks and worker behavior.
