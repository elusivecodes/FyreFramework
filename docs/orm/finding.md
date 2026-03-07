# Finding Data

Finding data in the ORM starts from `Fyre\ORM\Model`. Use `Model::find()` to build a `Fyre\ORM\Queries\SelectQuery`, `Model::get()` to fetch a single entity by primary key, and `Fyre\ORM\Result` to iterate hydrated entities.

## Table of Contents

- [Purpose](#purpose)
- [Where finding fits](#where-finding-fits)
- [Finding many records](#finding-many-records)
  - [Building a `SelectQuery`](#building-a-selectquery)
  - [Common query options](#common-query-options)
  - [Getting entities vs raw rows](#getting-entities-vs-raw-rows)
  - [Loading related data with `contain()`](#loading-related-data-with-contain)
  - [Filtering by relationships](#filtering-by-relationships)
  - [Getting a subset of results](#getting-a-subset-of-results)
- [Finding one record](#finding-one-record)
- [Working with `Result`](#working-with-result)
  - [Buffering vs streaming](#buffering-vs-streaming)
  - [Result metadata and cleanup](#result-metadata-and-cleanup)
  - [Forwarded collection methods](#forwarded-collection-methods)
- [Find events](#find-events)
- [Method guide](#method-guide)
  - [`Model`](#model)
  - [`SelectQuery`](#selectquery)
  - [`Result`](#result)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use the ORM finding APIs when you want model-aware queries (schema-driven auto-fields, relationship loading, and entity hydration) while still working with familiar query-builder concepts like conditions, joins, ordering, and limits.

Most examples assume you already have a model instance (for example, `$Users`). When an example uses a different model variable (for example, `$Memberships`), assume it exists too.

## Where finding fits

- `Model::find()` returns a `Fyre\ORM\Queries\SelectQuery` (an ORM-aware wrapper around the database select query builder).
- `SelectQuery::all()` / `SelectQuery::getResult()` return a `Fyre\ORM\Result`, which maps result rows into entities.
- `Model::get()` is a convenience wrapper around `find()` for primary-key lookups and returns `Entity|null`.

For the underlying query builder syntax (conditions, joins, ordering, grouping, and SQL compilation), see [Database queries](../database/queries.md). For entity field access and `_matchingData`, see [Entities](entities.md).

## Finding many records

### Building a `SelectQuery`

`find()` returns a `SelectQuery` you can continue to refine using query-builder methods:

```php
$result = $Users->find()
    ->where(['Users.id >' => 10])
    ->orderBy('Users.id DESC')
    ->all();

foreach ($result as $user) {
    $id = $user->get('id');
    $name = $user->get('name');
}
```

### Common query options

`Model::find()` (and `Model::get()`) accept a set of optional parameters that map directly to `SelectQuery` builder methods:

- `fields` → `select()`
- `contain` → `contain()`
- `join` → `join()`
- `conditions` → `where()`
- `orderBy` → `orderBy()`
- `groupBy` → `groupBy()`
- `having` → `having()`
- `limit` → `limit()`
- `offset` → `offset()`
- `epilog` → `epilog()`

Named arguments work well here:

```php
$users = $Users->find(
    conditions: ['Users.id >' => 10],
    orderBy: 'Users.id DESC',
    limit: 50
)->toArray();
```

### Getting entities vs raw rows

When you want hydrated entities, prefer `all()` / `getResult()` / `toArray()` over calling `execute()` directly:

- `SelectQuery::execute()` returns a `Fyre\DB\ResultSet` of raw rows (arrays).
- `SelectQuery::all()` returns a `Fyre\ORM\Result` that maps rows into entities (and can apply `contain()` and `_matchingData` hydration).

### Loading related data with `contain()`

`contain()` tells the ORM to load relationships alongside the primary rows. Relationship names come from your model relationship definitions (see [ORM Relationships](relationships.md)).

Contain supports:

- Nested paths (for example `Posts.Comments`)
- Array forms for per-relationship options

How related data is loaded depends on the contain strategy:

- **Default strategies** (`select`, `subquery`, `cte`): related data is loaded using additional queries.
- **Join loading** (`strategy => 'join'`): the query is expanded with joins and joined columns are hydrated into relationship properties on the entity.

```php
$users = $Users->find()
    ->contain('Addresses')
    ->where(['Users.id IN' => [1, 2, 3]])
    ->toArray();

$property = $Users->getRelationship('Addresses')->getProperty();
$addresses = $users[0]->get($property);
```

### Filtering by relationships

When filtering based on related rows, use relationship join helpers:

- `matching()` performs an `INNER` join and hydrates matching data under `_matchingData`.
- `notMatching()` excludes rows that have a match (using a `NOT EXISTS (...)` subquery).
- `leftJoinWith()` / `innerJoinWith()` join relationship tables without hydrating `_matchingData`.

```php
$result = $Users->find()
    ->matching('Posts', ['Posts.title LIKE' => '%SQL%'])
    ->all();

foreach ($result as $user) {
    $matching = $user->get('_matchingData');
    $post = $matching['Posts'] ?? null;
}
```

### Getting a subset of results

Use `count()` and `first()` when you only need a subset:

- `count()` counts the current query (including any `LIMIT`/`OFFSET`) by wrapping it as a subquery and removing `ORDER BY`.
- `first()` returns the first entity (and applies `LIMIT 1` when results are not already loaded).

```php
$total = $Users->find(conditions: ['Users.id >' => 10])->count();
$first = $Users->find(conditions: ['Users.id >' => 10])->first();
```

## Finding one record

`Model::get()` retrieves a single entity by the model primary key(s). It builds a `find()` query, adds primary key conditions, and returns `first()`.

If the record does not exist, `get()` returns `null`.

```php
$user = $Users->get(10, contain: 'Addresses');
```

If the model uses a composite primary key, pass an array of values in primary-key order:

```php
$membership = $Memberships->get([10, 25]);
```

## Working with `Result`

`Fyre\ORM\Result` wraps a database `ResultSet` and turns each row into an entity (including contained data and `_matchingData` when applicable).

You can iterate the result directly:

```php
foreach ($Users->find()->all() as $user) {
    $name = $user->get('name');
}
```

### Buffering vs streaming

By default, results are buffered: entities are cached in memory so you can iterate multiple times without re-executing the query.

If you disable buffering on the query, iteration becomes streaming:

- entities are produced one-by-one
- the underlying cursor is freed once exhausted (or if you call `free()`)
- when using non-join contain paths, related data can be loaded incrementally during iteration

```php
$result = $Users->find(contain: 'Addresses')
    ->disableBuffering()
    ->all();

foreach ($result as $user) {
    // ...
}
```

### Result metadata and cleanup

`Result` exposes a small set of direct helpers:

- `Result::columns()` and `Result::columnCount()` expose result-set metadata.
- `Result::getType($name)` returns the database type handler for a column (when available).
- `Result::free()` releases resources early and stops streaming iteration.
- `Result::fetch($index)` reads an entity at an index (but can advance the cursor when streaming).

### Forwarded collection methods

`Result` forwards unknown method calls to its underlying `Fyre\Utility\Collection` of entities. This lets you use collection helpers without manually converting to an array.

## Find events

When events are enabled for a query (the default), `SelectQuery` triggers:

- `ORM.beforeFind` once when the query is prepared (for example when executing, counting, or generating SQL)
- `ORM.afterFind` when the query result is first materialized and wrapped into a `Result`

To learn how to listen using `#[BeforeFind]` / `#[AfterFind]` attributes or event-manager listeners, see [ORM Events](events.md).

## Method guide

This is a quick reference for common query and result operations. For lower-level query builder details, see [Database queries](../database/queries.md).

### `Model`

#### **Build a select query** (`find()`)

Create an ORM-aware `SelectQuery`.

Arguments:
- `$fields` (`array<mixed>|string|null`): the `SELECT` fields.
- `$contain` (`array<mixed>|string|null`): relationships to load via `contain()`.
- `$conditions` (`array<mixed>|string|null`): the `WHERE` conditions.
- `$orderBy` (`array<string>|string|null`): the `ORDER BY` fields.
- `$limit` (`int|null`): the `LIMIT` clause.
- `$offset` (`int|null`): the `OFFSET` clause.

```php
$query = $Users->find(conditions: ['Users.active' => 1]);
```

#### **Fetch a single entity by primary key** (`get()`)

Look up a single entity by primary key(s) and return the first match (or `null`).

Arguments:
- `$primaryValues` (`array<int|string>|int|string`): the primary key value(s).

```php
$user = $Users->get(10);
```

### `SelectQuery`

#### **Hydrate all results** (`all()`)

Execute the query (if needed) and return a `Result` of hydrated entities.

```php
$result = $Users->find()->all();
```

#### **Return hydrated entities as an array** (`toArray()`)

Execute the query (if needed) and return all hydrated entities as an array.

```php
$users = $Users->find()->toArray();
```

#### **Execute and return raw rows** (`execute()`)

Execute the query and return the underlying `Fyre\DB\ResultSet` of raw rows.

```php
$rows = $Users->find()->execute();
```

#### **Load relationships** (`contain()`)

Configure related data loading for the query.

Arguments:
- `$contain` (`array<mixed>|string`): the contain relationships.
- `$overwrite` (`bool`): whether to replace existing contain configuration.

```php
$query = $Users->find()->contain('Addresses');
```

#### **Require related matches** (`matching()`)

`INNER` join a relationship and hydrate matching data under `_matchingData`.

Arguments:
- `$contain` (`string`): the relationship path.
- `$conditions` (`array<mixed>`): extra join conditions.

```php
$query = $Users->find()->matching('Posts', ['Posts.published' => 1]);
```

#### **Exclude related matches** (`notMatching()`)

Exclude rows that have a related match (using a `NOT EXISTS (...)` subquery).

Arguments:
- `$contain` (`string`): the relationship path.
- `$conditions` (`array<mixed>`): extra join conditions.

```php
$query = $Users->find()->notMatching('Posts', ['Posts.published' => 0]);
```

#### **Join without matching hydration** (`leftJoinWith()`)

`LEFT` join a relationship table without hydrating `_matchingData`.

Arguments:
- `$contain` (`string`): the relationship path.
- `$conditions` (`array<mixed>`): extra join conditions.

```php
$query = $Users->find()->leftJoinWith('Posts');
```

#### **Join without matching hydration** (`innerJoinWith()`)

`INNER` join a relationship table without hydrating `_matchingData`.

Arguments:
- `$contain` (`string`): the relationship path.
- `$conditions` (`array<mixed>`): extra join conditions.

```php
$query = $Users->find()->innerJoinWith('Posts');
```

#### **Return the first entity** (`first()`)

Return the first hydrated entity (or `null`).

```php
$first = $Users->find()->first();
```

#### **Count the current query** (`count()`)

Count the current query (including any applied `LIMIT`/`OFFSET`).

```php
$total = $Users->find()->count();
```

#### **Stream results instead of buffering** (`disableBuffering()`)

Disable buffering so the `Result` streams entities during iteration.

```php
$result = $Users->find()->disableBuffering()->all();
```

### `Result`

#### **Read an entity by index** (`fetch()`)

Iterate until the given index is reached and return that entity (or `null`).

Arguments:
- `$index` (`int`): the zero-based index.

```php
$result = $Users->find()->all();
$user = $result->fetch(0);
```

#### **Free resources early** (`free()`)

Release the underlying `ResultSet` and stop streaming iteration.

```php
$result = $Users->find()->disableBuffering()->all();
$result->free();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `SelectQuery::getResult()` caches the `Result` until the query is dirtied (for example by adding conditions, joins, fields, or contain configuration).
- `SelectQuery::count()` counts the *current* query, including any applied `LIMIT`/`OFFSET`.
- Generating SQL with `SelectQuery::sql()` prepares the query (auto-fields and contain/join expansion); by default it resets the prepared state after SQL generation.
- When buffering is disabled, `Result::fetch()` may advance the underlying cursor, and `Result::free()` stops streaming iteration.

## Related

- [Models](models.md)
- [Entities](entities.md)
- [ORM Relationships](relationships.md)
- [ORM Events](events.md)
- [Database queries](../database/queries.md)
