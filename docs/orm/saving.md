# Saving Data

Saving in the ORM is entity-first: you build or patch entities from input data, then persist them through `Fyre\ORM\Model` using `save()` or `saveMany()`. For bulk updates, `updateAll()` modifies matching rows directly without hydrating entities.

## Table of Contents

- [Purpose](#purpose)
- [Workflow overview](#workflow-overview)
- [Building entities from input](#building-entities-from-input)
  - [Creating empty entities](#creating-empty-entities)
  - [Building new entities](#building-new-entities)
  - [Patching existing entities](#patching-existing-entities)
- [Validation and rule sets](#validation-and-rule-sets)
- [Saving entities](#saving-entities)
  - [Saving one entity](#saving-one-entity)
  - [Saving many entities](#saving-many-entities)
  - [Saving related entities](#saving-related-entities)
  - [Primary key population](#primary-key-population)
  - [Handling errors](#handling-errors)
- [Bulk updates with `updateAll()`](#bulk-updates-with-updateall)
- [Events and hooks](#events-and-hooks)
- [Method guide](#method-guide)
  - [Entity building](#entity-building)
  - [Saving](#saving)
  - [Bulk updates](#bulk-updates)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use model-driven entity building and saving when you want schema-aware parsing, field guarding, validation, relationship handling, and transactional persistence.

## Workflow overview

Most examples assume you already have a model instance (for example, `$Users`). When an example uses a different model variable (for example, `$Articles`), assume it exists too.

The ORM’s write workflow generally looks like this:

1. Build or patch entities with `newEntity()` / `patchEntity()` (and their plural variants).
2. Review validation errors on the entity (if validation is enabled).
3. Persist with `save()` / `saveMany()` (optionally running rule sets and saving related entities).

If you need to update many rows and don’t need entity-level behavior, use `updateAll()` instead.

## Building entities from input

Creating entities through a model ensures they have the correct source and participate in model-driven behavior such as schema parsing, guarding, validation, and relationship injection.

These workflows are controlled by a set of common flags:

- **Schema parsing** (`$parse`): when enabled, values are converted using the model schema types (and parse events can run).
- **Guarding** (`$guard` and `$accessible`): when enabled, only accessible fields are set from input.
- **Validation** (`$validate`): when enabled, the model validator validates the input and populates entity errors. See [Form Validators](../form/validators.md).
- **Relationships** (`$associated`): when enabled, nested relationship data may be injected into the entity graph.

### Creating empty entities

`newEmptyEntity()` creates a blank entity instance for the model. This is useful for “new record” workflows (for example, form defaults) without applying any input data.

```php
$user = $Users->newEmptyEntity();
```

### Building new entities

`newEntity()` and `newEntities()` build new entities from input data and apply the “user input” workflow (parsing, guarding, validation, and relationship injection) based on the flags you pass.

```php
$user = $Users->newEntity(
    [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]
);
```

### Patching existing entities

`patchEntity()` updates an existing entity in-place using the same workflow as `newEntity()` (and using the `update` validation type when validation is enabled).

```php
$user = $Users->get(10);
if ($user) {
    $Users->patchEntity($user, ['name' => 'Ada']);
}
```

## Validation and rule sets

Validation and rule sets both produce errors on entities, but they run at different times and serve different purposes:

- **Validation** runs when building or patching entities (when `$validate` is enabled). It validates user input shape and constraints (required fields, formats, lengths) and writes errors onto the entity. See [Form Validators](../form/validators.md).
- **Rule sets** run during `save()` / `saveMany()` (when `$checkRules` is enabled). They enforce model-level integrity that may require database context (for example uniqueness and foreign key existence) and can also write errors onto the entity. See [Rule Sets](rulesets.md).

If an entity already has errors, `save()` / `saveMany()` return `false` without attempting persistence.

## Saving entities

### Saving one entity

`save()` persists a single entity in a transaction.

Important behaviors:

- If the entity is not new and not dirty, `save()` returns `true` without issuing queries.
- If the entity has errors, `save()` returns `false`.
- When enabled, existence checks run for “new” entities that already have primary key values.
- When enabled, rule sets run as part of the save workflow. See [Rule Sets](rulesets.md).
- The save runs inside a transaction; failures roll back and clear temporary field changes on the entity graph.

```php
$user = $Users->newEntity(['email' => 'ada@example.com']);

if ($Users->save($user)) {
    // saved
}
```

### Saving many entities

`saveMany()` persists multiple entities as a single unit inside one transaction.

Important behaviors:

- Entities that are neither new nor dirty are filtered out before saving.
- If the filtered list is empty, `saveMany()` returns `true`.
- If any entity has errors, `saveMany()` returns `false`.
- Any failure rolls back all changes.

```php
$users = $Users->newEntities([
    ['email' => 'a@example.com'],
    ['email' => 'b@example.com'],
]);

$Users->saveMany($users);
```

### Saving related entities

When saving related entities is enabled (the default), a model saves relationships in two phases:

1. **Parents first**: relationships where the current entity stores the foreign key (for example `BelongsTo`).
2. **Children after**: relationships where the related entity (or a junction table) stores the link (for example `HasOne`, `HasMany`, `ManyToMany`).

In both phases, the ORM sets relationship keys as *temporary* values during the transaction (for example foreign keys on children, or a belongs-to foreign key on the source entity). If the save fails, those temporary values are cleared as part of the rollback.

To build an entity graph from input, you must:

- include relationship names in the `$associated` option, and
- provide nested data using relationship **property names** (by default, the underscored relationship name; singular for single relations, plural for multiple).

```php
$article = $Articles->newEntity(
    [
        'title' => 'Saving graphs',

        // belongsTo('Users') typically maps to the `user` property.
        'user' => [
            'email' => 'author@example.com',
        ],

        // hasMany('Comments') typically maps to the `comments` property.
        'comments' => [
            ['body' => 'First!'],
            ['body' => 'Nice post'],
        ],
    ],
    associated: ['Users', 'Comments']
);

$Articles->save($article);
```

Many-to-many relationships can also include `_joinData` for junction table fields:

```php
$article = $Articles->newEntity(
    [
        'title' => 'Tagging',
        'tags' => [
            ['name' => 'orm', '_joinData' => ['weight' => 10]],
            ['name' => 'php'],
        ],
    ],
    associated: ['Tags']
);

$Articles->save($article);
```

To persist only the primary entity and ignore any nested relationship graph, pass `saveRelated: false`:

```php
$Articles->save($article, saveRelated: false);
```

### Primary key population

When inserting a new entity, the ORM populates missing primary key values on the entity after the insert completes:

- if the insert returns a row containing primary key values, those values are applied
- if the table has an auto-increment primary key and it was not provided, the ORM uses the connection insert id

Primary keys populated during a save are set as temporary values during the transaction. After a successful commit, entity cleaning (when enabled) clears the temporary status and marks the entity as not new.

```php
$user = $Users->newEntity(['email' => 'ada@example.com']);
$Users->save($user);

// Assuming `id` is the auto-increment primary key:
$id = $user->get('id');
```

### Handling errors

Errors are stored on the entity (and can include nested errors for related entities). Common patterns are:

- check validation errors after `newEntity()` / `patchEntity()`
- check rule set errors after a failed `save()` / `saveMany()`

```php
$user = $Users->newEntity(['email' => 'not-an-email']);

if ($user->hasErrors()) {
    $errors = $user->getErrors();
}

if (!$Users->save($user)) {
    $errors = $user->getErrors();
}
```

## Bulk updates with `updateAll()`

`updateAll(array $data, array $conditions): int` updates all rows matching the conditions and returns the number of rows affected.

It is implemented using an ORM `UpdateQuery` wrapper over the database query builder, so it:

- does not hydrate entities
- does not run model validation or rule sets
- does not run ORM save/parse events

```php
$affected = $Users->updateAll(
    ['active' => 0],
    ['Users.last_login <' => '2025-01-01']
);
```

## Events and hooks

Entity building and saving can dispatch ORM lifecycle events. The attribute-based listener system provides convenient hooks you can annotate on your model. See [ORM Events](events.md).

Saving-related attributes include:

- `#[BeforeParse]` and `#[AfterParse]` when parsing is enabled while building or patching entities.
- `#[BeforeRules]` and `#[AfterRules]` when rule checking is enabled during saves.
- `#[BeforeSave]`, `#[AfterSave]`, and `#[AfterSaveCommit]` around persistence and transaction commit.

## Method guide

This is a quick reference for the most common calls involved in building and saving entities. For full model behavior and query APIs, see [Models](models.md) and [Finding Data](finding.md).

### Entity building

#### **Create an empty entity** (`newEmptyEntity()`)

Creates a blank entity instance for this model.

Arguments: none

```php
$user = $Users->newEmptyEntity();
```

#### **Build a new entity from input** (`newEntity()`)

Creates a new entity and injects user input into it (optionally parsing, guarding, validating, and injecting relationships).

Arguments:
- `$data` (`array<string, mixed>`): input data to apply to the entity.
- `$associated` (`array<mixed>|string|null`): relationships to accept nested data for.
- `$accessible` (`array<string, bool>|null`): per-call accessibility overrides (applied only when guarding is enabled).
- `$guard` (`bool`): whether to enforce field accessibility.
- `$mutate` (`bool`): whether to allow entity mutations while setting fields.
- `$validate` (`bool`): whether to validate and populate entity errors.
- `$parse` (`bool`): whether to parse values using the model schema types.
- `$events` (`bool`): whether to dispatch parse events during this workflow.
- `$clean` (`bool`): whether to clean the entity after injecting data.
- `$new` (`bool|null`): whether to explicitly set the entity “new” state.
- `$options` (`mixed`): additional entity options.

```php
$user = $Users->newEntity(
    ['email' => 'ada@example.com'],
    validate: true
);
```

#### **Build many new entities from input** (`newEntities()`)

Builds multiple new entities from a list of input arrays.

Arguments:
- `$data` (`array<array<string, mixed>>`): input arrays (one per entity).
- `$associated` (`array<mixed>|string|null`): relationships to accept nested data for.
- `$accessible` (`array<string, bool>|null`): per-call accessibility overrides (applied only when guarding is enabled).
- `$guard` (`bool`): whether to enforce field accessibility.
- `$mutate` (`bool`): whether to allow entity mutations while setting fields.
- `$validate` (`bool`): whether to validate and populate entity errors.
- `$parse` (`bool`): whether to parse values using the model schema types.
- `$events` (`bool`): whether to dispatch parse events during this workflow.
- `$clean` (`bool`): whether to clean each entity after injecting data.
- `$new` (`bool|null`): whether to explicitly set each entity “new” state.
- `$options` (`mixed`): additional entity options.

```php
$users = $Users->newEntities([
    ['email' => 'a@example.com'],
    ['email' => 'b@example.com'],
]);
```

#### **Patch an existing entity from input** (`patchEntity()`)

Updates an existing entity in-place (optionally parsing, guarding, validating, and injecting relationships).

Arguments:
- `$entity` (`Entity`): the entity to update.
- `$data` (`array<string, mixed>`): input data to apply.
- `$associated` (`array<mixed>|string|null`): relationships to accept nested data for.
- `$accessible` (`array<string, bool>|null`): per-call accessibility overrides (applied only when guarding is enabled).
- `$guard` (`bool`): whether to enforce field accessibility.
- `$mutate` (`bool`): whether to allow entity mutations while setting fields.
- `$validate` (`bool`): whether to validate and populate entity errors.
- `$parse` (`bool`): whether to parse values using the model schema types.
- `$events` (`bool`): whether to dispatch parse events during this workflow.
- `$clean` (`bool`): whether to clean the entity after injecting data.
- `$new` (`bool|null`): whether to explicitly set the entity “new” state.
- `$options` (`mixed`): additional entity options.

```php
$user = $Users->get(10);
if ($user) {
    $Users->patchEntity($user, ['name' => 'Ada']);
}
```

#### **Patch many existing entities from input** (`patchEntities()`)

Patches many entities from a parallel list of input arrays. Input is matched by index: `$data[0]` patches the first entity, and so on.

Arguments:
- `$entities` (`iterable<Entity>`): the entities to patch.
- `$data` (`array<array<string, mixed>>`): input arrays matched by index.
- `$associated` (`array<mixed>|string|null`): relationships to accept nested data for.
- `$accessible` (`array<string, bool>|null`): per-call accessibility overrides (applied only when guarding is enabled).
- `$guard` (`bool`): whether to enforce field accessibility.
- `$mutate` (`bool`): whether to allow entity mutations while setting fields.
- `$validate` (`bool`): whether to validate and populate entity errors.
- `$parse` (`bool`): whether to parse values using the model schema types.
- `$events` (`bool`): whether to dispatch parse events during this workflow.
- `$clean` (`bool`): whether to clean entities after injecting data.
- `$new` (`bool|null`): whether to explicitly set the entity “new” state.
- `$options` (`mixed`): additional entity options.

```php
$users = $Users->find()->limit(2)->all();
$Users->patchEntities($users, [
    ['active' => 1],
    ['active' => 0],
]);
```

### Saving

#### **Save a single entity** (`save()`)

Persists an entity (insert or update) and optionally saves related entities. The operation runs inside a transaction.

Arguments:
- `$entity` (`Entity`): the entity to save.
- `$saveRelated` (`bool`): whether to save related entities.
- `$checkRules` (`bool`): whether to run model rule sets during the save. See [Rule Sets](rulesets.md).
- `$checkExists` (`bool`): whether to run an existence check before saving.
- `$events` (`bool`): whether to dispatch ORM save events.
- `$clean` (`bool`): whether to clean entities after commit.
- `$options` (`mixed`): additional save options.

```php
$user = $Users->newEntity(['email' => 'ada@example.com']);

if (!$Users->save($user, checkRules: true)) {
    $errors = $user->getErrors();
}
```

#### **Save multiple entities** (`saveMany()`)

Saves multiple entities as a single unit. The operation runs inside a single transaction.

Arguments:
- `$entities` (`iterable<Entity>`): entities to save.
- `$saveRelated` (`bool`): whether to save related entities.
- `$checkRules` (`bool`): whether to run model rule sets during saves.
- `$checkExists` (`bool`): whether to run existence checks before saving.
- `$events` (`bool`): whether to dispatch ORM save events.
- `$clean` (`bool`): whether to clean entities after commit.
- `$options` (`mixed`): additional save options.

```php
$users = $Users->newEntities([
    ['email' => 'a@example.com'],
    ['email' => 'b@example.com'],
]);

$Users->saveMany($users);
```

### Bulk updates

#### **Update many rows without entities** (`updateAll()`)

Updates all rows matching the conditions and returns the number of rows affected.

Arguments:
- `$data` (`array<string, mixed>`): column values to set.
- `$conditions` (`array<mixed>`): conditions to match.

```php
$affected = $Users->updateAll(
    ['active' => 0],
    ['Users.last_login <' => '2025-01-01']
);
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `newEntity()` / `patchEntity()` run validation with the model validator when `$validate` is enabled and write errors onto the entity. See [Form Validators](../form/validators.md).
- Parse events (`ORM.beforeParse` / `ORM.afterParse`) run only when both `$parse` and `$events` are enabled.
- `save()` returns early (and does not issue queries) when an entity is not new and has no dirty fields.
- `save()` / `saveMany()` return `false` when the entity graph has errors.
- When `$checkRules` is enabled, rule sets can add errors during `save()` / `saveMany()` and cause the save to return `false`. See [Rule Sets](rulesets.md).
- When saving related entities is enabled, belongs-to relations are saved before the main entity, and owning-side relations are saved after.
- Primary keys and relationship keys populated during a save are set as temporary values during the transaction; a failed save clears them, and successful post-commit cleaning (when enabled) clears the temporary status and marks entities as not new.
- `updateAll()` bypasses entity parsing, validation, rule sets, relationship saving, and ORM events.

## Related

- [Models](models.md)
- [Entities](entities.md)
- [Form Validators](../form/validators.md)
- [Rule Sets](rulesets.md)
- [ORM Events](events.md)
- [Database queries](../database/queries.md)
