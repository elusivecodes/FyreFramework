# ORM Traits

`Fyre\ORM\Traits\SoftDeleteTrait` and `Fyre\ORM\Traits\TimestampsTrait` are opt-in model traits that add common behavior through ORM events. You can also write your own traits to share reusable model helpers or ORM event listeners.

- `SoftDeleteTrait` — soft deletes, restore, and “include deleted” query helpers.
- `TimestampsTrait` — automatic `created`/`modified` timestamp updates on save.

## Table of Contents

- [Purpose](#purpose)
- [SoftDeleteTrait](#softdeletetrait)
  - [How it works](#how-it-works)
  - [Query helpers](#query-helpers)
  - [Delete vs purge](#delete-vs-purge)
  - [Restore](#restore)
  - [Configuration](#configuration)
- [TimestampsTrait](#timestampstrait)
  - [How timestamps work](#how-timestamps-work)
  - [Timestamp configuration](#timestamp-configuration)
- [Custom traits](#custom-traits)
- [Method guide](#method-guide)
  - [SoftDeleteTrait methods](#softdeletetrait-methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use traits to share model behavior that is enforced consistently via ORM events (for example, soft deletes or automatic timestamps), while keeping the behavior close to the model.

Most examples assume you already have a model instance (for example, `$Users`).

## SoftDeleteTrait

`SoftDeleteTrait` adds soft-delete behavior to a model by listening for `ORM.beforeDelete` and `ORM.beforeFind`. For a deeper overview of the delete lifecycle, see [Deleting Data](deleting.md). For the underlying attribute-based listener mechanism, see [ORM Events](events.md).

### How it works

Soft deletes don’t remove rows. Instead, the trait intercepts deletes and sets a configured “deleted” field to the current timestamp (`DateTime::now()`), then saves the entity.

It also intercepts queries and, by default, filters out deleted rows:

- on find, adds `...deleted_field IS NULL` unless the `deleted` find option is enabled.

### Query helpers

The trait adds helpers that set the `deleted` find option and apply the appropriate condition:

- `findWithDeleted(...)` — returns all records, including soft-deleted ones.
- `findOnlyDeleted(...)` — returns only soft-deleted records.

Both methods are wrappers around `Model::find(..., deleted: true)`:

```php
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

class UsersModel extends Model
{
    use SoftDeleteTrait;
}
```

When using a model that has the trait enabled, you can use the helpers (assume `$Users` is your model instance):

```php
// Default: excludes deleted rows.
$active = $Users->find()->toArray();

// Includes deleted rows.
$withDeleted = $Users->findWithDeleted()->toArray();

// Only deleted rows.
$deleted = $Users->findOnlyDeleted()->toArray();
```

### Delete vs purge

When the trait is enabled, calling `Model::delete($entity)` performs a soft delete unless you explicitly purge:

- `delete($entity, ..., purge: false)` → soft delete (default)
- `purge($entity, ...)` → hard delete (permanent)

The trait implements `purge()` and `purgeMany()` as wrappers around `delete()` / `deleteMany()` with `purge: true`.

When cascading soft deletes, dependent `hasOne`/`hasMany` relationships that also use `SoftDeleteTrait` are unlinked before the delete completes.

### Restore

The trait adds:

- `restore($entity, ...)`
- `restoreMany($entities, ...)`

Restore clears the deleted field (sets it to `null`) and saves. When restoring dependents, it:

- finds dependent `hasOne`/`hasMany` children that are deleted, and
- restores them in the same transaction (only when the target model also uses `SoftDeleteTrait`).

### Configuration

Override these properties in your model to change column names:

- `$deletedField` (default: `'deleted'`)

```php
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

class PostsModel extends Model
{
    use SoftDeleteTrait;

    protected string $deletedField = 'deleted_at';
}
```

## TimestampsTrait

### How timestamps work

On save, the trait sets timestamps to `DateTime::now()`:

- If the entity is new and the schema has the `$createdField` column, it sets that field.
- If the schema has the `$modifiedField` column, it sets that field on every save.

Both fields are set as temporary values on the entity (`temporary: true`) right before persistence.

### Timestamp configuration

Override these properties in your model to change column names:

- `$createdField` (default: `'created'`)
- `$modifiedField` (default: `'modified'`)

```php
use Fyre\ORM\Model;
use Fyre\ORM\Traits\TimestampsTrait;

class UsersModel extends Model
{
    use TimestampsTrait;

    protected string $createdField = 'created_at';
    protected string $modifiedField = 'updated_at';
}
```

## Custom traits

You can write your own PHP traits and apply them to models to share reusable logic. This is especially useful for ORM event listeners: because a model registers itself as a listener, methods contributed by traits are discovered the same way as methods defined directly on the model (see [ORM Events](events.md)).

For example, you can bundle a `#[BeforeSave]` listener into a trait:

```php
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\BeforeSave;

trait AuditTrait
{
    #[BeforeSave]
    public function setAuditFields(Event $event, Entity $entity, array $options): void
    {
        $entity->set('modified_by', 123);
    }
}
```

Then apply it to a model:

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    use AuditTrait;
}
```

## Method guide

This guide focuses on the public helper methods added by `SoftDeleteTrait`. `TimestampsTrait` does not add public helper methods; it is configured via `$createdField` / `$modifiedField`.

### SoftDeleteTrait methods

#### **Find with deleted** (`findWithDeleted()`)

Returns a `SelectQuery` that includes soft-deleted rows (equivalent to calling `find(..., deleted: true)`).

```php
$withDeleted = $Users->findWithDeleted()->toArray();
```

#### **Find only deleted** (`findOnlyDeleted()`)

Returns a `SelectQuery` filtered to only soft-deleted rows.

```php
$deleted = $Users->findOnlyDeleted()->toArray();
```

#### **Restore** (`restore()`)

Clears the deleted field (sets it to `null`) and saves the entity, restoring it from a soft delete.

Arguments:
- `$entity` (`Entity`): the entity to restore.
- `$cascade` (`bool`): whether to restore related children.
- `$events` (`bool`): whether to trigger events.
- `...$options` (`mixed`) Additional save options.

```php
$Users->restore($entity);
```

#### **Restore many** (`restoreMany()`)

Restores many entities from soft deletes.

Arguments:
- `$entities` (`iterable<Entity>`): the entities to restore.
- `$cascade` (`bool`): whether to restore related children.
- `$events` (`bool`): whether to trigger events.
- `...$options` (`mixed`) Additional save options.

```php
$Users->restoreMany($entities);
```

#### **Purge** (`purge()`)

Permanently deletes an entity (a hard delete).

Arguments:
- `$entity` (`Entity`): the entity to delete.
- `$cascade` (`bool`): whether to delete related children.
- `$events` (`bool`): whether to trigger events.
- `...$options` (`mixed`) Additional delete options.

```php
if (!$Users->purge($entity)) {
    // handle failure
}
```

#### **Purge many** (`purgeMany()`)

Permanently deletes many entities (a hard delete).

Arguments:
- `$entities` (`iterable<Entity>`): the entities to delete.
- `$cascade` (`bool`): whether to delete related children.
- `$events` (`bool`): whether to trigger events.
- `...$options` (`mixed`) Additional delete options.

```php
$Users->purgeMany($entities);
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Soft delete is implemented in a `BeforeDelete` event handler; if you call `delete(..., events: false)` you’ll bypass the trait and perform a hard delete.
- Default querying filters deleted rows via a `BeforeFind` handler. If you need deleted rows, use `findWithDeleted()` / `findOnlyDeleted()` (or pass `deleted: true` when building a query).
- Soft delete sets the deleted field as a temporary value on the entity (`temporary: true`) before saving.
- Restore runs inside a transaction. If dependent restore fails, the entire restore operation rolls back.
- Timestamping is implemented in a `BeforeSave` event handler; if you call `save(..., events: false)` you’ll bypass the trait.
- Timestamps are only set when the relevant column exists in the model schema.

## Related

- [Models](models.md)
- [Finding Data](finding.md)
- [ORM Events](events.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
