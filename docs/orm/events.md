# ORM Events

`Fyre\ORM\Model` dispatches ORM events (`ORM.*`) around querying, entity parsing, and persistence so you can enforce model-specific rules, apply defaults, or react to lifecycle changes without tightly coupling your application logic.

For the underlying event system (listeners, priorities, propagation), see [Events](../events/index.md). For ORM basics, see [Models](models.md) and [Entities](entities.md).

## Table of Contents

- [Purpose](#purpose)
- [Where ORM events fit](#where-orm-events-fit)
- [Per-model event managers](#per-model-event-managers)
- [Listening with ORM event attributes](#listening-with-orm-event-attributes)
- [Built-in ORM events](#built-in-orm-events)
  - [Find events](#find-events)
  - [Parsing events](#parsing-events)
  - [Save events](#save-events)
  - [Delete events](#delete-events)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use ORM events when you want to enforce model behavior at lifecycle boundaries (querying, parsing, saving, deleting) without scattering the logic across controllers or services.

Common uses include:

- normalizing or defaulting input during entity parsing
- blocking invalid saves/deletes and attaching entity errors
- adding shared model behavior like timestamps, soft deletes, or audit trails
- observing queries and results for logging/metrics

## Where ORM events fit

Every `Fyre\ORM\Model` dispatches events named `ORM.*` from the points where work is performed (query preparation/execution, entity parsing, save/delete lifecycles).

Two patterns are common:

- **Model-local behavior**: attach listeners to a model’s event manager (ideal for defaults, invariants, auditing, and model-specific policy).
- **Shared behavior across models**: attach listeners to a parent `Fyre\Event\EventManager` to observe the same ORM events from multiple models. Parent listeners run after the model’s listeners unless propagation is stopped; the dispatched `Fyre\Event\Event` uses the model as its subject.

## Per-model event managers

Each `Model` builds its own `Fyre\Event\EventManager` and configures the injected event manager as the parent. This keeps listeners scoped by default, while still allowing parent-level listeners to observe the same events.

The most common entry point is `Model::getEventManager()`.

Registering listeners directly on the model is a straightforward way to keep behavior close to the data:

```php
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->getEventManager()->on('ORM.beforeSave', function(Event $event, Entity $entity, array $options): void {
            $entity->set('updated', time());
        });
    }
}
```

## Listening with ORM event attributes

`Fyre\ORM\Events\*` provides attributes that map directly to ORM event names (for example `#[BeforeSave]` → `ORM.beforeSave`). Because the model registers itself as a listener, any annotated methods on the model (including methods contributed by traits) are discovered automatically.

Listener methods receive:

- `Fyre\Event\Event $event` as the first parameter
- the payload values in the documented order for each event

```php
use ArrayObject;
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\BeforeParse;
use Fyre\ORM\Events\BeforeSave;
use Fyre\ORM\Model;

class UsersModel extends Model
{
    #[BeforeParse]
    public function normalizeInput(Event $event, ArrayObject $data, array $options): void
    {
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = strtolower($data['email']);
        }
    }

    #[BeforeSave]
    public function preventInactiveSaves(Event $event, Entity $entity, array $options): void
    {
        if ($entity->get('active') === false) {
            $event->setResult(false);
        }
    }
}
```

## Built-in ORM events

All events are dispatched as `Fyre\Event\Event` with the model as the subject. The payload below lists the additional arguments a listener receives after the `Event` itself.

### Find events

- `ORM.beforeFind` — fired once when a `Fyre\ORM\Queries\SelectQuery` is prepared (for example when executing, counting, or generating SQL).
  - Payload: `Fyre\ORM\Queries\SelectQuery $query`, `array $options`
- `ORM.afterFind` — fired when the query result is first materialized and wrapped into a `Fyre\ORM\Result`.
  - Payload: `Fyre\ORM\Result $result`, `array $options`

### Parsing events

- `ORM.beforeParse` — fired before schema parsing when building or patching an entity; `$data` is mutable.
  - Payload: `ArrayObject $data`, `array $options`
- `ORM.afterParse` — fired after parsed data and relationships have been applied to the entity.
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`

### Save events

- `ORM.beforeRules` — fired before rules are evaluated during `Model::save()` when rule checking is enabled.
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterRules` — fired after rules validation passes (still within the save transaction).
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.beforeSave` — fired immediately before persistence begins (still within the save transaction).
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterSave` — fired after persistence and related saves complete (still within the save transaction).
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterSaveCommit` — fired after the save transaction commits.
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`

### Delete events

- `ORM.beforeDelete` — fired before the delete operation begins (within the delete transaction).
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterDelete` — fired after deletion and cascades complete (still within the delete transaction).
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterDeleteCommit` — fired after the delete transaction commits.
  - Payload: `Fyre\ORM\Entity $entity`, `array $options`

## Behavior notes

A few behaviors are worth keeping in mind:

- `ORM.beforeRules`, `ORM.afterRules`, `ORM.beforeSave`, `ORM.afterSave`, `ORM.beforeDelete`, and `ORM.afterDelete` are checked by the ORM to determine whether to continue. If propagation is stopped, the model returns `(bool) $event->getResult()` (and a `false` result will roll back the surrounding transaction). Find/parse/commit events do not short-circuit ORM work.
- Listener callbacks receive the values of the event data (`array_values()`), not the keys, so signatures must match the documented order for each event.
- `ORM.afterSaveCommit` / `ORM.afterDeleteCommit` are dispatched before the model’s post-commit entity cleaning runs (when enabled), so entities may still be “new” until cleaning completes.
- `Model::save()` returns early (and does not dispatch save events) when the entity is not new and has no dirty fields.

## Related

- [Events](../events/index.md)
- [Models](models.md)
- [Entities](entities.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
