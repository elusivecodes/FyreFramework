# Models

Use a model when you want one place to query a table, build entities, define relationships, and save or delete records.

## Table of Contents

- [Start here](#start-here)
- [Working with models](#working-with-models)
  - [Model identity](#model-identity)
  - [Building entities from a model](#building-entities-from-a-model)
  - [Model validation](#model-validation)
  - [Saving and deleting entities](#saving-and-deleting-entities)
- [Resolving models](#resolving-models)
  - [Using `ModelRegistry`](#using-modelregistry)
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

Relationship properties must not use the same names as table columns.

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

When `$associated` is `null`, every relationship defined directly on the model is eligible for processing.

If a field should hydrate and marshal as a PHP enum, map the field to its enum class using `EnumField`:

```php
use App\Enums\Status;
use Fyre\ORM\Attributes\EnumField;
use Fyre\ORM\Model;

#[EnumField('status', Status::class)]
class ArticlesModel extends Model {}
```

When selecting relationships, model relationship names can be expressed using dot-notation strings and nested arrays. For querying and eager-loading, see [Finding Data](finding.md).

Unknown relationship names passed through `contain` or `associated` options raise an exception.

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

`getValidator()` lazily creates a validator through the container and passes it to `buildValidator()`. Use `setValidator()` when you need to replace that instance directly. For validator APIs and built-in rules, see [Form Validators](../form/validators.md) and [Validation rules](../form/rules.md).

### Saving and deleting entities

Persistence operations are entity-first:

- `save(Entity $entity, ...)` inserts or updates based on entity state, can optionally save related data, and can optionally clean the entity after commit.
- `delete(Entity $entity, ...)` deletes the record and can optionally cascade into owning-side relationships.

Errors prevent new or dirty entities from being saved. Existing entities with no changes return `true` without issuing queries; this check happens before errors are inspected. For validation concepts and rule building, see [Form Validators](../form/validators.md) and [Rule Sets](rulesets.md).

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
$ArchivedUsers = $modelRegistry->use('ArchivedUsers', 'Users');
```

The second argument to `use()` selects a different class alias while retaining the requested runtime alias. Aliases are enforced: reusing the same alias with a different class alias raises an exception.

| Method | Purpose |
| --- | --- |
| `use($alias, $classAlias = null)` | resolve and return a shared model instance |
| `isLoaded($alias)` | check whether an alias has been resolved |
| `unload($alias)` | remove one shared model instance |
| `clear()` | remove every namespace and shared model instance |

If you use contextual injection, `#[ORM('Users')]` can resolve a model by alias while the container is building an object or calling a callable; see [Contextual attributes](../core/contextual-attributes.md).

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
