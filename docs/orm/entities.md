# Entities

`Fyre\ORM\Entity` represents an individual record. It holds field values, tracks change state, collects validation errors, and serializes to arrays or JSON.

For the persistence layer (tables, relationships, validation/rules hooks, and query helpers), see [Models](models.md).

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Creating entities](#creating-entities)
- [Field access and guarding](#field-access-and-guarding)
- [Change tracking and original values](#change-tracking-and-original-values)
- [Errors and invalid values](#errors-and-invalid-values)
- [Serialization](#serialization)
- [Method guide](#method-guide)
  - [Entity methods](#entity-methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use an entity when you want a record object that can be validated, saved, serialized, and passed through save/delete workflows with dirty tracking and error state.

## Mental model

A `Fyre\ORM\Entity` is a record-centric object:

- holds fields and relationships as values
- enforces field accessibility when you opt into guarding
- tracks dirty fields and preserves original values
- collects validation errors (including nested error trees)
- serializes to arrays/JSON with support for hidden and virtual fields

## Creating entities

In normal ORM usage, you don’t manually construct entities. Instead, create and hydrate them through a model:

- `find()` returns entities when results are hydrated.
- `newEmptyEntity()` creates a blank entity for a model.
- `newEntity()` and `patchEntity()` apply input workflows (schema parsing, guarding, mutation hooks, and validation).

For the full workflows, see [Models](models.md), [Finding Data](finding.md), and [Saving Data](saving.md).

If you use custom entity subclasses, entity class resolution is handled via the ORM’s `EntityLocator` conventions. In most applications, the only direct customization you need is adding additional namespaces to search.

## Field access and guarding

Entities expose fields through methods, magic accessors, and array access:

- `$entity->get('field')` / `$entity->set('field', $value)`
- `$entity->field` and `$entity['field']` both delegate to the same access layer

Field accessibility is opt-in: `set()` and `fill()` can enforce accessibility when guarding is enabled. Control accessibility with `setAccess()` and check it with `isAccessible()`.

By default, `fill()` checks accessibility (`$guard = true`), while `set()` does not (`$guard = false`).

Mutation hooks are available when you subclass `Fyre\ORM\Entity`. If the concrete entity class defines a method in the form `_{Prefix}{Field}` (camelized), it will be invoked:

- read hook: `'_getFieldName'` is applied during `get()`
- write hook: `'_setFieldName'` is applied during `set()` (and `fill()`)

## Change tracking and original values

Entities track change state automatically:

- `set()` marks a field dirty when a value actually changes.
- `isDirty()` returns whether any field is dirty; `isDirty('field')` checks a specific field.
- `getDirty()` returns the list of dirty field names.
- `getOriginal('field')` returns the pre-change value (or the current value when fallback is allowed).

Cleaning resets state for persisted entities:

- `clean()` clears dirty state, errors/invalid values, and sets current fields as original.

## Errors and invalid values

Errors can be attached to fields, and can also be nested through related entities/arrays:

- `setError()` / `setErrors()` assign validation errors to fields.
- `getError('field')` reads a single field’s errors.
- `getError('parent.child')` traverses dot notation through nested entities/arrays and returns the nested errors.
- `hasErrors(true)` considers nested entity errors as well as direct errors.

Invalid values can be stored separately from fields:

- `setInvalid('field', $value)` stores an invalid input value.
- `getInvalid()` returns all invalid values, or `getInvalid('field')` for one.

## Serialization

Serialization is driven by visibility rules:

- `setHidden([...])` hides fields from `toArray()` / `toJson()`.
- `setVirtual([...])` adds extra field names to the “visible” list.
- `toArray(true)` converts nested entities recursively and also converts `JsonSerializable`/`Stringable` values where possible.
- `toJson()` returns a pretty-printed JSON representation.

## Method guide

### Entity methods

Most examples assume you already have an `$entity` instance (for example, one returned by `Model::get()` or from `Model::find()->all()`).

#### **Read a field** (`get()`)

Return a field value from the entity.

Arguments:
- `$field` (`string`): the field name.

```php
$name = $entity->get('name');
```

#### **Set a field** (`set()`)

Set a field value, optionally enforcing accessibility and applying mutation hooks.

Arguments:
- `$field` (`string`): the field name.
- `$value` (`mixed`): the value.
- `$guard` (`bool`): whether to enforce accessibility.
- `$mutate` (`bool`): whether to apply mutation hooks.

```php
$entity->set('name', 'Ada', guard: true);
```

#### **Fill multiple fields** (`fill()`)

Set many fields from an input array.

Arguments:
- `$data` (`array`): the data to fill.
- `$guard` (`bool`): whether to enforce accessibility.
- `$mutate` (`bool`): whether to apply mutation hooks.

```php
$entity->fill(['name' => 'Ada', 'email' => 'ada@example.com'], guard: true);
```

#### **Check whether a field exists** (`has()`)

Check whether a key exists in the entity’s fields array.

Arguments:
- `$field` (`string`): the field name.

```php
if ($entity->has('name')) {
    // ...
}
```

#### **Work with dirty fields** (`isDirty()`)

Check whether the entity (or a specific field) has changed since it was created or cleaned.

Arguments:
- `$field` (`string|null`): the field name, or `null` to check any field.

```php
$entity->clean();

$entity->set('name', 'Ada Lovelace');

$isDirty = $entity->isDirty('name');
```

#### **List dirty fields** (`getDirty()`)

Get the list of field names currently marked dirty.

```php
$entity->clean();

$entity->set('name', 'Ada Lovelace');

$dirty = $entity->getDirty();
```

#### **Read an original value** (`getOriginal()`)

Read the pre-change value for a field (when available).

Arguments:
- `$field` (`string|null`): the field name, or `null` to return current fields merged with any stored original values.
- `$fallback` (`bool`): whether to fall back to the current value when no original exists.

```php
$entity->clean();

$entity->set('name', 'Ada Lovelace');

$original = $entity->getOriginal('name');
```

#### **Attach validation errors** (`setErrors()`)

Attach validation errors to fields.

Arguments:
- `$errors` (`array`): an array of field => error(s).
- `$overwrite` (`bool`): whether to overwrite existing errors.

```php
$entity->setErrors(['email' => ['Invalid email address']]);
```

#### **Read all validation errors** (`getErrors()`)

Return all errors on the entity, including errors nested within related entities/arrays.

```php
$entity->setErrors(['email' => ['Invalid email address']]);

$errors = $entity->getErrors();
```

#### **Read errors for a field** (`getError()`)

Return errors for a specific field. Dot notation traverses into nested entities/arrays.

Arguments:
- `$field` (`string`): the field name (optionally using dot notation).

```php
$entity->setErrors(['email' => ['Invalid email address']]);

$emailErrors = $entity->getError('email');
```

#### **Serialize to an array** (`toArray()`)

Convert the entity to an array using visibility rules. Nested entities are converted recursively.

Arguments:
- `$convertObjects` (`bool`): whether to convert `JsonSerializable` and `Stringable` objects where possible.

```php
$data = $entity->toArray();
```

#### **Serialize to JSON** (`toJson()`)

Convert the entity to a pretty-printed JSON string.

```php
$json = $entity->toJson();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Mutation hooks only run on subclasses (not on the base `Entity` class).
- `set()` with guarding enabled silently skips inaccessible fields.
- `getError('a.b.c')` returns an empty array if any intermediate segment is missing.
- `getOriginal('field', fallback: false)` throws an `InvalidArgumentException` when no original value exists.

## Related

- [Models](models.md)
- [Finding Data](finding.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
- [ORM Relationships](relationships.md)
- [Form Validators](../form/validators.md)
- [Rule Sets](rulesets.md)
- [ORM Events](events.md)
