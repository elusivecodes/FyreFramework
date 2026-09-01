# ORM Traits

Use ORM traits when you want reusable model behavior such as soft deletes or automatic timestamps.

You can also write your own traits to share model helpers or event-based behavior across models.

## Table of Contents

- [Start here](#start-here)
- [`SoftDeleteTrait`](#softdeletetrait)
  - [Soft delete behavior](#soft-delete-behavior)
  - [Query helpers](#query-helpers)
  - [Delete vs purge](#delete-vs-purge)
  - [Restore](#restore)
  - [Configuration](#configuration)
- [`TimestampsTrait`](#timestampstrait)
  - [When timestamps are set](#when-timestamps-are-set)
  - [Timestamp configuration](#timestamp-configuration)
- [Custom traits](#custom-traits)
- [Related](#related)

## Start here

The built-in traits cover two common cases:

- `SoftDeleteTrait` for deleted-at style records and restore or purge helpers
- `TimestampsTrait` for automatic created and modified fields

The examples use a model instance named `$Users`; see [Models](models.md) for model resolution and configuration.

## `SoftDeleteTrait`

`SoftDeleteTrait` turns deletes into timestamp updates and filters deleted rows from normal queries.

### Soft delete behavior

Soft deletes do not remove rows. Instead, the trait sets a configured deleted field to the current timestamp and saves the entity.

Normal `find()` queries exclude deleted rows unless you opt in to include them.

The trait implements this through a `BeforeDelete` listener and sets the deleted field as a temporary entity value. Calling `delete(..., events: false)` bypasses the listener and permanently deletes the row.

### Query helpers

The trait adds helpers that change that default:

- `findWithDeleted(...)` — returns all records, including soft-deleted ones.
- `findOnlyDeleted(...)` — returns only soft-deleted records.

Both methods are wrappers around `Model::find(..., deleted: true)`.

They accept the same query arguments as `Model::find()`, including `conditions` and `having` callbacks.

The default exclusion is applied by a `BeforeFind` listener. Queries built with `events: false` bypass it; use the helpers when you intentionally need deleted rows.

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

Both purge methods accept the same `cascade` and `events` options as their corresponding delete methods.

When cascading soft deletes, dependent `hasOne`/`hasMany` relationships that also use `SoftDeleteTrait` are unlinked before the delete completes.

### Restore

The trait adds:

- `restore($entity, ...)`
- `restoreMany($entities, ...)`

Restore clears the deleted field (sets it to `null`) and saves. When restoring dependents, it:

- finds dependent `hasOne`/`hasMany` children that are deleted, and
- restores them in the same transaction (only when the target model also uses `SoftDeleteTrait`).

`restore()` and `restoreMany()` accept the normal save options (`saveRelated`, `checkRules`, `checkExists`, `events`, and `clean`) plus `dependents`. All default to `true`.

```php
$user = $Users->findOnlyDeleted()->first();
if ($user) {
    $Users->restore($user);
}
```

If restoring an entity or one of its dependents fails, the transaction is rolled back.

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

## `TimestampsTrait`

### When timestamps are set

When a save proceeds to persistence, the trait sets timestamps to `DateTime::now()`:

- If the entity is new and the schema has the `$createdField` column, it sets that field.
- If the schema has the `$modifiedField` column, it sets that field.

Both fields are set as temporary values on the entity (`temporary: true`) right before persistence.

Saving an existing entity with no dirty fields returns before the `BeforeSave` event, so its timestamps are not changed.

Timestamping is implemented by that event listener, so `save(..., events: false)` bypasses it.

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

You can write your own PHP traits and apply them to models to share reusable logic. This is especially useful for ORM event listeners, since trait methods can use the same event attributes as methods defined directly on the model.

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

## Related

- [Models](models.md)
- [Finding Data](finding.md)
- [ORM Events](events.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
