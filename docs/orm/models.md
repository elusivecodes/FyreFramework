# Models

Use a model when you want one place to query a table, build entities, define relationships, and save or delete records.

Most application code starts from a model instance such as `$Users`.

## Table of Contents

- [Start here](#start-here)
- [Working with models](#working-with-models)
  - [Model identity](#model-identity)
  - [Building entities from a model](#building-entities-from-a-model)
  - [Model validation](#model-validation)
  - [Saving and deleting entities](#saving-and-deleting-entities)
- [Resolving models](#resolving-models)
  - [Using `ModelRegistry`](#using-modelregistry)
- [Method guide](#method-guide)
  - [Querying](#querying)
  - [Entity building](#entity-building)
  - [Persistence](#persistence)
  - [Configuration](#configuration)
  - [Validation](#validation)
  - [`ModelRegistry`](#modelregistry)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use a model when you want to:

- start queries with `find()` or `get()`
- build new or patched entities through `newEntity()` and `patchEntity()`
- define relationships in one place
- save or delete records through model methods

Most examples on this page assume you already have a model instance such as `$Users`.

```php
$Users = model('Users');
```

For record objects and field state, see [Entities](entities.md).

## Working with models

A model is the main ORM entry point for a table.

### Model identity

Models have two related identities:

- **Class alias**: usually the model class name with `Model` trimmed.
- **Alias**: the name used for query aliasing and relationship lookup. If you do not set one, it defaults to the class alias.

Table metadata is derived from the model and schema:

- `getTable()` defaults to an underscored form of the class alias.
- `getEntityClass()` resolves the entity class from the class alias.
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

Creating entities through a model assigns the runtime model alias and enables model-driven behavior like schema parsing and validation:

- `newEmptyEntity()` creates a blank entity for the model.
- `newEntity()` builds an entity from user data.
- `patchEntity()` applies user data onto an existing entity.
- `newEntities()` / `patchEntities()` apply the same workflow to multiple records.

Methods that accept entities require the entity class configured for the model. Models with different aliases but the same entity class can operate on the same entities.

By default, `newEntity()` and `patchEntity()` perform a full “user input” workflow:

- optional schema parsing (`parseSchema()`) so values are converted using column types
- configured schema enum classes also convert parsed values into enum cases
- optional field guarding (accessibility)
- optional mutation hooks on the entity
- optional validation (and error population)
- optional association handling via the `$associated` option

If a field should hydrate and marshal as a PHP enum, map the field to its enum class using `EnumField`:

```php
use App\Enums\Status;
use Fyre\ORM\Attributes\EnumField;
use Fyre\ORM\Model;

#[EnumField('status', Status::class)]
class ArticlesModel extends Model {}
```

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

### Model validation

Models define validation by overriding `buildValidator(Validator $validator): Validator`. `newEntity()` and `patchEntity()` use that validator when `$validate` is enabled.

Use model validation for input-shape and field-level checks such as required fields, lengths, formats, and custom per-field rules. Use [Rule Sets](rulesets.md) for integrity checks that depend on database state.

```php
use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function buildValidator(Validator $validator): Validator
    {
        return $validator
            ->add('email', Rule::email(), name: 'email')
            ->add('email', Rule::required(), name: 'required')
            ->add('name', Rule::maxLength(255), name: 'maxLength');
    }
}
```

If you need to inspect or replace the validator instance directly, use `getValidator()` or `setValidator()`. For validator APIs and built-in rules, see [Form Validators](../form/validators.md) and [Validation rules](../form/rules.md).

### Saving and deleting entities

Persistence operations are entity-first:

- `save(Entity $entity, ...)` inserts or updates based on entity state, can optionally save related data, and can optionally clean the entity after commit.
- `delete(Entity $entity, ...)` deletes the record and can optionally cascade into owning-side relationships.

Errors prevent new or dirty entities from being saved. Existing entities with no changes are skipped before errors are checked. For validation concepts and rule building, see [Form Validators](../form/validators.md) and [Rule Sets](rulesets.md).

```php
$user = $Users->get(10);
if ($user) {
    $Users->patchEntity($user, ['name' => 'Ada']);
    $Users->save($user);
}
```

## Resolving models

### Using `ModelRegistry`

Use `model()` or `ModelRegistry` when you want to resolve a model by alias.

In the default `Engine` setup, `ModelRegistry` already includes the `App\Models` namespace; see [Engine](../core/engine.md). If you add more namespaces, the registry can resolve `<ClassAlias>Model` classes from those namespaces too.

```php
$modelRegistry->addNamespace('App\Models');

$Users = $modelRegistry->use('Users');
```

Aliases are enforced: reusing the same alias with a different class alias will raise an exception.

If you use contextual injection, `#[ORM('Users')]` can resolve a model by alias while the container is building an object or calling a callable; see [Contextual attributes](../core/contextual-attributes.md).

## Method guide

This is a quick reference for commonly used APIs. For full workflows, see [Finding Data](finding.md), [Saving Data](saving.md), and [Deleting Data](deleting.md).

### Querying

#### **Build a query** (`find()`)

Create a new `SelectQuery` scoped to the model (table aliasing, relationship names, and entity hydration). For a walkthrough of common query patterns, see [Finding Data](finding.md).

Arguments:
- `$fields` (`array|string|null`): the `SELECT` fields.
- `$contain` (`array|string|null`): relationships to contain.
- `$conditions` (`array|Closure|ConditionExpression|string|null`): the `WHERE` conditions.
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
- `$associated` (`array|string|null`): relationships to accept and configure; when `null`, every relationship defined directly on the model is eligible.
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

Insert or update an entity based on its state. When an entity is new or dirty, saving returns `false` if it or any nested entity has errors; clean existing entities are skipped first. For related saves, rules, and bulk saves, see [Saving Data](saving.md) and [Rule Sets](rulesets.md).

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

### Validation

#### **Access the model validator** (`getValidator()`)

Retrieve the lazily-built validator instance for the model. On first access, the model creates a `Fyre\Form\Validator` via the container and passes it to `buildValidator()`.

```php
$validator = $Users->getValidator();
```

### `ModelRegistry`

#### **Load a model by alias** (`use()`)

Load a model by alias. If `$classAlias` is provided and differs from `$alias`, the registry loads the `<ClassAlias>Model` class and assigns the alias you requested.

Arguments:
- `$alias` (`string`): the shared alias key.
- `$classAlias` (`string|null`): the model class alias to resolve.

```php
$Users = $modelRegistry->use('Users');
$ArchivedUsers = $modelRegistry->use('ArchivedUsers', 'Users');
```

#### **Add a model namespace** (`addNamespace()`)

Register a namespace to search for `<ClassAlias>Model` classes.

Arguments:
- `$namespace` (`string`): the namespace to add.

```php
$modelRegistry->addNamespace('App\Models');
```

#### **Unload a model** (`unload()`)

Remove a loaded model from the registry.

Arguments:
- `$alias` (`string`): the alias to unload.

```php
$modelRegistry->unload('Users');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Unknown relationship names passed through contain/associated options raise an exception.
- Relationship properties must not collide with table columns; conflicting relationships are rejected.
- `save()` returns `true` without issuing queries when an existing entity has no dirty fields.

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
- [Helpers](../core/helpers.md)
- [Contextual attributes](../core/contextual-attributes.md)
- [Engine](../core/engine.md)
