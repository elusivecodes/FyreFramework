# Deleting Data

Use entity deletes when you want relationship-aware deletes, lifecycle events, or soft-delete behavior.

Use `deleteAll()` when you want a direct bulk delete without entities.

## Table of Contents

- [Start here](#start-here)
- [Deleting entities](#deleting-entities)
  - [`delete()`](#delete)
  - [`deleteMany()`](#deletemany)
- [Cascading into relationships](#cascading-into-relationships)
- [Bulk deletes with `deleteAll()`](#bulk-deletes-with-deleteall)
- [Soft deletes with `SoftDeleteTrait`](#soft-deletes-with-softdeletetrait)
- [Delete events](#delete-events)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Choose the delete style based on what you need:

- use `delete()` or `deleteMany()` when callbacks, cascades, or traits should run
- use `deleteAll()` when you want a direct table delete
- use `SoftDeleteTrait` when deletes should mark rows instead of removing them

Most examples on this page assume you already have a model instance such as `$Users`.

## Deleting entities

### `delete()`

`delete(Entity $entity, bool $cascade = true, bool $events = true, ...$options): bool` deletes a single entity.

If `$cascade` is enabled, the delete can cascade into owning-side relationships (see [Cascading into relationships](#cascading-into-relationships)).

When `$events` is enabled, the model dispatches `ORM.beforeDelete` and `ORM.afterDelete` during the transaction, and `ORM.afterDeleteCommit` after the transaction commits (see [ORM Events](events.md)).

```php
$user = $Users->get(10);
if ($user) {
    $Users->delete($user, cascade: true);
}
```

### `deleteMany()`

`deleteMany(iterable $entities, bool $cascade = true, bool $events = true, ...$options): bool` deletes multiple entities in a single transaction.

If the iterable is empty, `deleteMany()` returns `true`. If any entity delete fails (including cascades), the transaction is rolled back and the method returns `false`.

```php
$entities = $Users->find()
    ->where(['Users.active' => 0])
    ->all();

$Users->deleteMany($entities);
```

## Cascading into relationships

When cascading is enabled (`cascade: true`), `delete()` / `deleteMany()` attempt to unlink related records for each owning-side relationship defined on the model (see [ORM Relationships](relationships.md)).

For each relationship, the ORM calls `Relationship::unlinkAll()`:

- If the relationship is marked dependent, *or* the relationship foreign key is not nullable, the related records are deleted (via the target model’s `deleteMany()`).
- Otherwise, the related records are unlinked by setting the foreign key to `null` and saving them.

## Bulk deletes with `deleteAll()`

`deleteAll(array|Closure|ConditionExpression|string $conditions): int` deletes all rows matching the conditions and returns the number of rows affected.

`deleteAll()` executes as a direct delete query, so it:

- does not require entities
- does not cascade into relationships
- does not dispatch `ORM.*Delete*` events

```php
$affected = $Sessions->deleteAll(['Sessions.expires <' => '2025-01-01']);
```

## Soft deletes with `SoftDeleteTrait`

Models that use `Fyre\ORM\Traits\SoftDeleteTrait` change what “delete” means: `delete()` / `deleteMany()` are intercepted by the trait’s `#[BeforeDelete]` listener and turned into a soft delete (the configured deleted field is set to the current timestamp and the entity is saved), unless `purge: true` is passed.

When soft deleting with cascading enabled, the trait unlinks dependent `HasOne` / `HasMany` relationships whose target models also use `SoftDeleteTrait`.

`SoftDeleteTrait` also provides `purge()` and `purgeMany()` convenience methods for permanent deletes.

## Delete events

To attach model methods to delete lifecycle events, use the ORM event attributes:

- `#[BeforeDelete]` for `ORM.beforeDelete`
- `#[AfterDelete]` for `ORM.afterDelete`
- `#[AfterDeleteCommit]` for `ORM.afterDeleteCommit`

For how attributes are discovered and what listener signatures receive, see [ORM Events](events.md).

```php
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\BeforeDelete;
use Fyre\ORM\Model;

class UsersModel extends Model
{
    #[BeforeDelete]
    public function preventDeletes(Event $event, Entity $entity, array $options): void
    {
        $event->setResult(false);
    }
}
```

## Behavior notes

- `delete()` and `deleteMany()` always run inside a transaction, so cascade failures roll back the whole delete.
- Entity deletes return `false` when no rows are affected (they call `deleteAll()` for the entity’s primary key conditions under the hood).
- `ORM.afterDeleteCommit` is dispatched after the transaction commits (and `deleteMany()` dispatches it once per entity when enabled).

## Related

- [ORM Relationships](relationships.md)
- [ORM Events](events.md)
- [Saving Data](saving.md)
- [Finding Data](finding.md)
- [Database queries](../database/queries.md)
