# ORM Events

Use ORM events when you want model behavior to run around finding, parsing, saving, or deleting records.

They are a good fit for timestamps, defaults, audit fields, and save or delete guards.

## Table of Contents

- [Start here](#start-here)
- [Common event patterns](#common-event-patterns)
- [Registering listeners](#registering-listeners)
- [Using event attributes](#using-event-attributes)
- [Built-in ORM events](#built-in-orm-events)
  - [Build events](#build-events)
  - [Find events](#find-events)
  - [Parsing events](#parsing-events)
  - [Save events](#save-events)
  - [Delete events](#delete-events)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use ORM events when you want to:

- normalize or default input during parsing
- block invalid saves or deletes
- add reusable model behavior such as timestamps or soft deletes
- observe model activity for logging or metrics

For the underlying event system, see [Events](../events/index.md). For ORM basics, see [Models](models.md) and [Entities](entities.md).

## Common event patterns

Two patterns are common:

- **Model-local behavior**: keep listeners on the model for defaults, invariants, and model-specific policy.
- **Reusable behavior**: put annotated listener methods in a trait and apply that trait to multiple models.

## Registering listeners

The most direct entry point is `Model::getEventManager()`.

Registering listeners directly on the model is a straightforward way to keep behavior close to the data:

```php
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->getEventManager()->on('ORM.beforeSave', static function(Event $event, Entity $entity, array $options): void {
            $entity->set('updated', time());
        });
    }
}
```

## Using event attributes

`Fyre\ORM\Events\*` provides attributes that map directly to ORM event names (for example `#[BeforeSave]` → `ORM.beforeSave`).

Listener methods receive:

- `Fyre\Event\Event $event` as the first parameter
- the event arguments in the documented order

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

All events below pass `Fyre\Event\Event` first, followed by the additional arguments listed for that event.

### Build events

- `ORM.buildValidator` — fired when `Model::getValidator()` first builds the model validator.
  - Arguments: `Fyre\Form\Validator $validator`
- `ORM.buildJoin` — fired when a relationship join is being built for join-strategy `contain()` loading or query helpers like `leftJoinWith()`, `innerJoinWith()`, and `matching()`; `$join` is mutable.
  - Arguments: `Fyre\ORM\Queries\SelectQuery $query`, `Fyre\ORM\Relationship $relationship`, `ArrayObject $join`, `string $mode`, `string $path`, `string $alias`, `string $sourceAlias`, `array $options`

### Find events

- `ORM.beforeFind` — fired once when a `Fyre\ORM\Queries\SelectQuery` is prepared (for example when executing, counting, or generating SQL).
  - Arguments: `Fyre\ORM\Queries\SelectQuery $query`, `array $options`
- `ORM.afterFind` — fired when the query result is first wrapped in a `Fyre\ORM\Result`.
  - Arguments: `Fyre\ORM\Result $result`, `array $options`

### Parsing events

- `ORM.beforeParse` — fired before schema parsing when building or patching an entity; `$data` is mutable.
  - Arguments: `ArrayObject $data`, `array $options`
- `ORM.afterParse` — fired after parsed data and relationships have been applied to the entity.
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`

### Save events

- `ORM.beforeRules` — fired before rules are evaluated during `Model::save()` when rule checking is enabled.
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterRules` — fired after rules validation passes (still within the save transaction).
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.beforeSave` — fired immediately before persistence begins (still within the save transaction).
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterSave` — fired after persistence and related saves complete (still within the save transaction).
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterSaveCommit` — fired after the save transaction commits.
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`

### Delete events

- `ORM.beforeDelete` — fired before the delete operation begins (within the delete transaction).
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterDelete` — fired after deletion and cascades complete (still within the delete transaction).
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`
- `ORM.afterDeleteCommit` — fired after the delete transaction commits.
  - Arguments: `Fyre\ORM\Entity $entity`, `array $options`

## Behavior notes

A few behaviors are worth keeping in mind:

- Stopping `ORM.beforeRules`, `ORM.afterRules`, `ORM.beforeSave`, `ORM.afterSave`, `ORM.beforeDelete`, or `ORM.afterDelete` can stop the ORM operation. Stopping find, parse, or commit events does not stop the operation, although find and parse listeners can still modify the mutable query or data they receive.
- Listener callbacks receive the values of the event data (`array_values()`), not the keys, so signatures must match the documented order for each event.
- `ORM.afterSaveCommit` / `ORM.afterDeleteCommit` are dispatched before the model’s post-commit entity cleaning runs (when enabled), so entities may still be “new” until cleaning completes.
- `Model::save()` returns early (and does not dispatch save events) when the entity is not new and has no dirty fields.

## Related

- [Events](../events/index.md)
- [Models](models.md)
- [Entities](entities.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
