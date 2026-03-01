# Models

`Fyre\ORM\Model` represents a table and provides ORM-aware queries and persistence for entities. `Fyre\ORM\ModelRegistry` locates and caches model instances by alias.

For record objects (field access, change tracking, errors, and serialization), see [Entities](entities.md).

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Models: persistence and metadata](#models-persistence-and-metadata)
  - [Identity: alias, class alias, and table](#identity-alias-class-alias-and-table)
  - [Building entities from a model](#building-entities-from-a-model)
  - [Saving and deleting entities](#saving-and-deleting-entities)
- [ModelRegistry](#modelregistry)
  - [Locating and sharing models](#locating-and-sharing-models)
- [Method guide](#method-guide)
  - [Querying](#querying)
  - [Entity building](#entity-building)
  - [Persistence](#persistence)
  - [Configuration](#configuration)
  - [Registry](#registry)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use a model when you want a table-aware API for querying and persisting entities, including relationship definitions and model-level behaviors (validation, rule sets, events).

Most examples assume you already have a model instance (for example, `$Users`).

## Mental model

🧠 At runtime, a model acts like a table-centric service:

- creates query objects (`find()`, `selectQuery()`, `insertQuery()`, `updateQuery()`, `deleteQuery()`)
- builds entities for the model alias (`newEntity()`, `newEmptyEntity()`, `patchEntity()`)
- saves and deletes entities (`save()`, `delete()`)

An entity is a record-centric object:

- holds fields and relationships as values
- tracks state (new/dirty/original) and validation errors
- serializes to arrays/JSON

Entities are documented in [Entities](entities.md). When you don’t explicitly wire classes, `ModelRegistry` and `EntityLocator` provide conventions and caching so models and entities can be resolved from simple aliases.

## Models: persistence and metadata

### Identity: alias, class alias, and table

Models have two related identities:

- **Class alias**: derived from the model class name (the short class name with `Model` trimmed). When the default model class is used, `Fyre\ORM\ModelRegistry` sets this explicitly.
- **Alias**: the runtime alias used for query aliasing and relationship lookup. If you don’t set one, it defaults to the class alias.

Table metadata is derived from the model and schema:

- `getTable()` defaults to an underscored form of the class alias.
- `getPrimaryKey()` defaults to the table schema primary key (or an empty array when none is available).
- `getDisplayName()` picks the first matching column from `name`, `title`, `label`, then the primary key(s).
- `getRouteKey()` picks `slug` if available, otherwise falls back to the primary key(s).

If you need to override the table name, use `setTable()`.

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->setTable('users');
    }
}
```

### Building entities from a model

Creating entities through a model ensures they have the correct source and participate in model-driven behavior like schema parsing and validation:

- `newEmptyEntity()` creates a blank entity for the model.
- `newEntity()` builds an entity from user data.
- `patchEntity()` applies user data onto an existing entity.
- `newEntities()` / `patchEntities()` apply the same workflow to multiple records.

By default, `newEntity()` and `patchEntity()` perform a full “user input” workflow:

- optional schema parsing (`parseSchema()`) so values are converted using column types
- optional field guarding (accessibility)
- optional mutation hooks on the entity
- optional validation (and error population)
- optional association handling via the `$associated` option

When selecting relationships, model relationship names can be expressed using dot-notation strings and nested arrays. For querying and eager-loading, see [Finding Data](finding.md).

```php
$user = $Users->newEntity(
    [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ],
    guard: true
);

$Users->save($user);
```

### Saving and deleting entities

Persistence operations are entity-first:

- `save(Entity $entity, ...)` inserts or updates based on entity state, can optionally save related data, and can optionally clean the entity after commit.
- `delete(Entity $entity, ...)` deletes the record and can optionally cascade into owning-side relationships.

Validation errors prevent saves; for validation concepts and rule building, see [Form Validators](../form/validators.md) and [Rule Sets](rulesets.md).

```php
$user = $Users->get(10);
if ($user) {
    $Users->patchEntity($user, ['name' => 'Ada']);
    $Users->save($user);
}
```

## ModelRegistry

### Locating and sharing models

`Fyre\ORM\ModelRegistry` is a cache for shared model instances keyed by alias.

This section assumes you already have a `ModelRegistry` instance available as `$modelRegistry`.

It can also locate specialized model classes when you add namespaces. For a class alias like `Users`, it searches for a `<ClassAlias>Model` class in each configured namespace; when none is found, it falls back to the default model class.

```php
$modelRegistry->addNamespace('App\Models');

$Users = $modelRegistry->use('Users');
```

Aliases are enforced: reusing the same alias with a different class alias will raise an exception.

## Method guide

This is a quick reference for commonly used APIs. For full workflows, see [Finding Data](finding.md), [Saving Data](saving.md), and [Deleting Data](deleting.md).

### Querying

#### **Build a query** (`find()`)

Create a new `SelectQuery` scoped to the model (table aliasing, relationship names, and entity hydration). For a walkthrough of common query patterns, see [Finding Data](finding.md).

Arguments:
- `$fields` (`array|string|null`): the `SELECT` fields.
- `$contain` (`array|string|null`): relationships to contain.
- `$conditions` (`array|string|null`): the `WHERE` conditions.
- `$orderBy` (`array|string|null`): the `ORDER BY` fields.
- `$limit` (`int|null`): the LIMIT clause.
- `$offset` (`int|null`): the OFFSET clause.

```php
$result = $Users->find()
    ->where(['Users.id >' => 10])
    ->orderBy('Users.id DESC')
    ->all();
```

#### **Fetch by primary key** (`get()`)

Fetch a single entity by primary key, or return `null` when no matching row exists.

Arguments:
- `$primaryValues` (`array|int|string`): the primary key value(s).
- `$contain` (`array|string|null`): relationships to contain.

```php
$user = $Users->get(10, contain: 'Profiles');
```

### Entity building

#### **Build an empty entity** (`newEmptyEntity()`)

Create a blank entity associated with the model.

```php
$user = $Users->newEmptyEntity();
```

#### **Build a new entity from input** (`newEntity()`)

Build an entity from user input, optionally parsing schema types, guarding fields, validating, and handling associated data. For the full input and saving workflow, see [Saving Data](saving.md).

Arguments:
- `$data` (`array`): the input data.
- `$associated` (`array|string|null`): associated relationships to allow.
- `$guard` (`bool`): whether to enforce accessibility.
- `$validate` (`bool`): whether to validate and populate errors.

```php
$user = $Users->newEntity(
    [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ],
    guard: true
);
```

#### **Patch an existing entity from input** (`patchEntity()`)

Apply user input onto an existing entity in-place. For bulk patching, see `patchEntities()` in [Saving Data](saving.md).

Arguments:
- `$entity` (`Entity`): the entity to update.
- `$data` (`array`): the input data.

```php
$user = $Users->get(10);
if ($user) {
    $Users->patchEntity($user, ['name' => 'Ada']);
}
```

### Persistence

#### **Save an entity** (`save()`)

Insert or update an entity based on its state. Saves return `false` when the entity has errors. For related saves, rules, and bulk saves, see [Saving Data](saving.md) and [Rule Sets](rulesets.md).

Arguments:
- `$entity` (`Entity`): the entity to persist.
- `$saveRelated` (`bool`): whether to save related entities.
- `$checkRules` (`bool`): whether to run the model rule set.

```php
$user = $Users->newEntity(['name' => 'Ada']);
$Users->save($user);
```

#### **Delete an entity** (`delete()`)

Delete an entity, optionally cascading into owning-side relationships. For bulk deletes and cascades, see [Deleting Data](deleting.md).

Arguments:
- `$entity` (`Entity`): the entity to delete.
- `$cascade` (`bool`): whether to delete related children.

```php
$user = $Users->get(10);
if ($user) {
    $Users->delete($user, cascade: true);
}
```

### Configuration

#### **Override the table name** (`setTable()`)

Set the table name used by the model.

Arguments:
- `$table` (`string`): the table name.

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->setTable('users');
    }
}
```

### Registry

#### **Load a shared model instance** (`use()`)

Load (and cache) a model by alias. If `$classAlias` is provided and differs from `$alias`, the registry loads the `<ClassAlias>Model` class and assigns the runtime alias.

Arguments:
- `$alias` (`string`): the shared alias key.
- `$classAlias` (`string|null`): the model class alias to resolve.

```php
$Users = $modelRegistry->use('Users');
$ArchivedUsers = $modelRegistry->use('ArchivedUsers', 'Users');
```

#### **Add a lookup namespace** (`addNamespace()`)

Register a namespace to search for `<ClassAlias>Model` classes.

Arguments:
- `$namespace` (`string`): the namespace to add.

```php
$modelRegistry->addNamespace('App\Models');
```

#### **Unload a model** (`unload()`)

Remove a cached model instance from the registry.

Arguments:
- `$alias` (`string`): the alias to unload.

```php
$modelRegistry->unload('Users');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Unknown relationship names passed through contain/associated options raise an exception.
- Relationship properties must not collide with table columns; conflicting relationships are rejected.
- `save()` short-circuits: an existing entity with no dirty fields saves successfully without issuing queries.

## Related

- [ORM](index.md)
- [Entities](entities.md)
- [Finding Data](finding.md)
- [ORM Relationships](relationships.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
- [Rule Sets](rulesets.md)
- [ORM Traits](traits.md)
- [ORM Events](events.md)
