# Entities

Use entities when you want record objects with fields, dirty tracking, errors, and serialization.

## Table of Contents

- [Start here](#start-here)
- [Creating entities](#creating-entities)
- [Field access and guarding](#field-access-and-guarding)
- [Change tracking and original values](#change-tracking-and-original-values)
- [Errors and invalid values](#errors-and-invalid-values)
- [Serialization](#serialization)
- [Related](#related)

## Start here

Entities are a good fit when you want to:

- work with records as objects instead of arrays
- track which fields changed
- attach validation errors to a record
- serialize a record back to an array or JSON

In normal ORM usage, create and patch entities through a model so schema parsing, guarding, validation, and relationship handling all run in one place.

## Creating entities

In normal ORM usage, you don’t manually construct entities. Instead, create and hydrate them through a model:

- `find()` returns entities when results are hydrated.
- `newEmptyEntity()` creates a blank entity for a model.
- `newEntity()` and `patchEntity()` apply input workflows (schema parsing, guarding, mutation hooks, and validation).

For the full workflows, see [Models](models.md), [Finding Data](finding.md), and [Saving Data](saving.md).

If you use custom entity subclasses, the ORM resolves them using the usual alias and namespace conventions. Entities created by a model retain its runtime alias, available through `getModelAlias()`.

## Field access and guarding

Entities expose fields through methods, magic accessors, and array access:

- `$entity->get('field')` / `$entity->set('field', $value)`
- `$entity->field` and `$entity['field']` both delegate to the same access layer

Use `has()` when you need to distinguish a missing field from one containing `null`.

```php
if ($entity->has('name')) {
    $name = $entity->get('name');
}

$entity->set('name', 'Ada');
$entity->fill([
    'email' => 'ada@example.com',
    'active' => true,
]);
```

Field accessibility is opt-in: `set()` and `fill()` can enforce accessibility when guarding is enabled. Control accessibility with `setAccess()` and check it with `isAccessible()`.

By default, `fill()` checks accessibility (`$guard = true`), while `set()` does not (`$guard = false`). When guarding is enabled, inaccessible fields are silently skipped.

Mutation hooks are available when you subclass `Fyre\ORM\Entity`. If the concrete entity class defines a method in the form `_{Prefix}{Field}` (camelized), it will be invoked:

- read hook: `'_getFieldName'` is applied during `get()`
- write hook: `'_setFieldName'` is applied during `set()` (and `fill()`)

Mutation hooks run only on subclasses, and can be skipped during writes with `mutate: false`.

## Change tracking and original values

Entities track change state automatically:

- `set()` marks a field dirty when a value actually changes.
- `isDirty()` returns whether any field is dirty; `isDirty('field')` checks a specific field.
- `getDirty()` returns the list of dirty field names.
- `getOriginal('field')` returns the pre-change value (or the current value when fallback is allowed).

```php
$entity->clean();
$entity->set('name', 'Ada Lovelace');

if ($entity->isDirty('name')) {
    $original = $entity->getOriginal('name');
}

$dirty = $entity->getDirty();
```

Pass `fallback: false` to `getOriginal()` when the absence of an original value should throw an `InvalidArgumentException`.

Cleaning resets state for persisted entities:

- `clean()` clears dirty state, errors/invalid values, and sets current fields as original.

## Errors and invalid values

Errors can be attached to fields, and can also be nested through related entities/arrays:

- `setError()` / `setErrors()` assign validation errors to fields.
- `getError('field')` reads a single field’s errors.
- `getError('parent.child')` traverses dot notation through nested entities/arrays and returns the nested errors.
- `hasErrors(true)` considers nested entity errors as well as direct errors.

`setError()` and `setErrors()` append to existing errors by default; pass `overwrite: true` to replace them.

```php
$entity->setErrors([
    'email' => ['Invalid email address'],
]);

$emailErrors = $entity->getError('email');
$errors = $entity->getErrors();
```

Nested `getError()` calls return an empty array when an intermediate segment is missing.

Invalid values can be stored separately from fields:

- `setInvalid('field', $value)` stores an invalid input value.
- `getInvalid()` returns all invalid values, or `getInvalid('field')` for one.

## Serialization

Serialization is driven by visibility rules:

- `setHidden([...])` hides fields from `toArray()` / `toJson()`.
- `setVirtual([...])` adds extra field names to the “visible” list.
- `toArray()` converts nested entities recursively.
- `toArray(true)` also converts `JsonSerializable` and `Stringable` values where possible.
- `toJson()` returns a pretty-printed JSON representation.

```php
$entity->setHidden(['password']);
$entity->setVirtual(['display_name']);

$data = $entity->toArray(true);
$json = $entity->toJson();
```

## Related

- [Models](models.md)
- [Finding Data](finding.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
- [ORM Relationships](relationships.md)
- [Form Validators](../form/validators.md)
- [Rule Sets](rulesets.md)
- [ORM Events](events.md)
