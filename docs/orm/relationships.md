# ORM Relationships

`Fyre\ORM\Relationship` defines how models connect, how related data is stored on entities, and how related records can be loaded, joined, saved, and unlinked through the ORM.

## Table of Contents

- [Purpose](#purpose)
- [Where relationships fit](#where-relationships-fit)
- [Defining relationships](#defining-relationships)
  - [Using `initialize()` helpers](#using-initialize-helpers)
  - [Using model attributes](#using-model-attributes)
  - [Relationship names and entity properties](#relationship-names-and-entity-properties)
- [Relationship types](#relationship-types)
  - [BelongsTo](#belongsto)
  - [HasOne](#hasone)
  - [HasMany](#hasmany)
  - [ManyToMany](#manytomany)
- [Common relationship options](#common-relationship-options)
- [Loading related data with `contain()`](#loading-related-data-with-contain)
  - [Contain shapes](#contain-shapes)
  - [Contain options](#contain-options)
  - [Contain callbacks](#contain-callbacks)
- [Loading strategies](#loading-strategies)
  - [Join loading: `join`](#join-loading-join)
  - [Eager loading: `select`, `subquery`, `cte`](#eager-loading-select-subquery-cte)
- [Filtering with relationship joins](#filtering-with-relationship-joins)
  - [`leftJoinWith()` and `innerJoinWith()`](#leftjoinwith-and-innerjoinwith)
  - [`matching()`](#matching)
  - [`notMatching()`](#notmatching)
- [Cascading deletes and unlinking](#cascading-deletes-and-unlinking)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use relationships when you want the ORM to understand how models connect so you can load related data with `contain()`, filter with relationship-aware joins, and coordinate saves/deletes across related entities.

Most examples assume you already have a model instance (for example, `$Users`).

## Where relationships fit

- A relationship is a model-level definition that ties a **source** model to a **target** model.
- Relationships drive:
  - `contain()` loading for queries (see [Finding Data](finding.md))
  - relationship-aware joins for filtering (`matching()`, `notMatching()`, `leftJoinWith()`, `innerJoinWith()`)
  - saving and unlinking related records when you save or delete entities (see [Saving Data](saving.md) and [Deleting Data](deleting.md))

## Defining relationships

### Using `initialize()` helpers

Define relationships in a model’s `initialize()` method using the relationship helpers:

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->hasOne('Addresses');
        $this->hasMany('Posts');
    }
}
```

### Using model attributes

You can also define relationships using model attributes. Attributes are loaded when the model is constructed, before `initialize()` runs, so you can mix both approaches.

```php
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Model;

#[HasOne('Addresses')]
#[HasMany('Posts')]
class UsersModel extends Model
{
    public function initialize(): void
    {
        // Additional configuration is still fine here.
        $this->hasMany('Comments', [
            'dependent' => true,
        ]);
    }
}
```

### Relationship names and entity properties

Each relationship has:

- a **relationship name** (the alias you pass to `hasMany('Posts')`)
- a **property name** (where the related data is stored on the entity)

By default, property names are derived from the relationship name:

- single-valued relationships (`belongsTo`, `hasOne`) use a singular underscored property (for example, `Addresses` → `address`)
- multi-valued relationships (`hasMany`, `manyToMany`) use a plural underscored property (for example, `Posts` → `posts`)

Override the property name with `propertyName`:

```php
use Fyre\ORM\Model;

class PostsModel extends Model
{
    public function initialize(): void
    {
        $this->belongsTo('Users', [
            'propertyName' => 'author',
        ]);
    }
}
```

## Relationship types

### BelongsTo

Use `belongsTo()` when the **source** model stores the foreign key and the related record is a single entity.

- Default strategy: `join` (also supports `select`)
- Foreign key default: derived from the relationship name (for example, `Users` → `user_id`)
- Binding key default: the target model’s first primary key column

Define it using `initialize()`:

```php
use Fyre\ORM\Model;

class PostsModel extends Model
{
    public function initialize(): void
    {
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }
}
```

Or define it using an attribute:

```php
use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;

#[BelongsTo('Users', ['foreignKey' => 'user_id'])]
class PostsModel extends Model {}
```

### HasOne

Use `hasOne()` when the **target** model stores the foreign key and the related record is a single entity.

- Default strategy: `join` (also supports `select`)
- Foreign key default: derived from the source model alias (for example, `Users` → `user_id`)
- Binding key default: the source model’s first primary key column

Define it using `initialize()`:

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->hasOne('Addresses', [
            'foreignKey' => 'user_id',
        ]);
    }
}
```

Or define it using an attribute:

```php
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Model;

#[HasOne('Addresses', ['foreignKey' => 'user_id'])]
class UsersModel extends Model {}
```

### HasMany

Use `hasMany()` when the **target** model stores the foreign key and the related records are a list of entities.

- Default strategy: `select` (also supports `subquery` and `cte`)
- Foreign key default: derived from the source model alias (for example, `Users` → `user_id`)
- Binding key default: the source model’s first primary key column
- Save strategies: `append` (default) or `replace`

Define it using `initialize()`:

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->hasMany('Posts', [
            'foreignKey' => 'user_id',
            'saveStrategy' => 'replace',
            'sort' => 'Posts.id DESC',
        ]);
    }
}
```

Or define it using an attribute:

```php
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Model;

#[HasMany('Posts', ['saveStrategy' => 'replace'])]
class UsersModel extends Model {}
```

### ManyToMany

Use `manyToMany()` when the source and target are linked through a junction (link) model/table.

- Default strategy: `select` (also supports `subquery` and `cte`)
- Junction model default (`through`): derived from the source model class alias and relationship name
- When loaded, junction row data is attached to each related entity under `_joinData`
- Save strategies: `append` or `replace` (default)

Define it using `initialize()`:

```php
use Fyre\ORM\Model;

class PostsModel extends Model
{
    public function initialize(): void
    {
        $this->manyToMany('Tags', [
            'through' => 'PostsTags',
            'foreignKey' => 'post_id',
            'targetForeignKey' => 'tag_id',
            'saveStrategy' => 'replace',
            'sort' => 'Tags.name ASC',
        ]);
    }
}
```

Or define it using an attribute:

```php
use Fyre\ORM\Attributes\ManyToMany;
use Fyre\ORM\Model;

#[ManyToMany('Tags', ['through' => 'PostsTags'])]
class PostsModel extends Model {}
```

## Common relationship options

All relationship types support these common options:

- `classAlias`: Map the relationship name to a different model class alias.
- `propertyName`: Override the entity property name used to store related data.
- `foreignKey`: Override the foreign key column name used for linking.
- `bindingKey`: Override the binding key column name used for linking.
- `strategy`: Set the default loading strategy for this relationship.
- `joinType`: Set the default join type used when building joins (defaults to `LEFT`).
- `conditions`: Add default conditions applied when joining or loading this relationship.
- `dependent`: When unlinking, delete related rows instead of nulling out the foreign key (when possible).

Some relationship types add additional options:

- `HasMany`: `saveStrategy` (`append` or `replace`), `sort`
- `ManyToMany`: `through`, `targetForeignKey`, `saveStrategy` (`append` or `replace`), `sort`

## Loading related data with `contain()`

Use `contain()` to load relationship data onto entities (see [Finding Data](finding.md)).

```php
$users = $Users->find()
    ->contain('Posts')
    ->toArray();

$posts = $users[0]->get('posts');
```

### Contain shapes

Contain accepts:

- dotted strings: `Posts.Comments`
- lists: `['Posts', 'Addresses']`
- nested arrays, where each relationship can have nested `contain`

```php
$users = $Users->find()
    ->contain([
        'Posts' => [
            'Comments' => [
                'Users',
            ],
        ],
    ])
    ->toArray();
```

### Contain options

For eager-loading strategies (`select`, `subquery`, `cte`), contain options are passed as named arguments to relationship loading:

- `fields`, `conditions`, `orderBy`, `groupBy`, `having`, `limit`, `offset`, `epilog`
- `contain` (nested relationships for the related query)
- `strategy` (override the loader strategy for this contain path)
- `autoFields` (control auto-field selection for the related query)
- `connectionType` (connection type for the related query)
- `callback` (a `Closure` that receives and returns a `SelectQuery`)

Example:

```php
$users = $Users->find()
    ->contain([
        'Posts' => [
            'conditions' => ['Posts.published' => true],
            'orderBy' => 'Posts.id DESC',
            'limit' => 10,
            'contain' => [
                'Comments' => [
                    'limit' => 3,
                ],
            ],
        ],
    ])
    ->toArray();
```

For join loading (`strategy` = `join`), the allowed option set is intentionally smaller:

- `strategy` (must be `join`)
- `type` (join type, defaults to the relationship join type)
- `conditions` (additional join conditions)
- `fields` (extra target fields to select)
- `autoFields` (whether to auto-select target schema columns)
- `contain` (nested contain)

### Contain callbacks

Callbacks run against the related `SelectQuery` right before it executes:

```php
use Fyre\ORM\Queries\SelectQuery;

$users = $Users->find()
    ->contain([
        'Posts' => [
            'callback' => static fn(SelectQuery $query): SelectQuery => $query->limit(5),
        ],
    ])
    ->toArray();
```

## Loading strategies

### Join loading: `join`

`join` expands the main query with relationship joins so related data is hydrated from the same result rows. It is the default strategy for `BelongsTo` and `HasOne`.

`join` is also available for filtering-only joins via query helpers (see [Filtering with relationship joins](#filtering-with-relationship-joins)).

### Eager loading: `select`, `subquery`, `cte`

Eager-loading strategies load related data using additional queries and then assign the results onto each entity’s relationship property.

Valid eager-loading strategies depend on relationship type:

- `HasMany` and `ManyToMany`: `select`, `subquery`, `cte`
- `BelongsTo` and `HasOne`: `select`

Set a default strategy on the relationship:

```php
use Fyre\ORM\Model;

class UsersModel extends Model
{
    public function initialize(): void
    {
        $this->hasMany('Posts')
            ->setStrategy('subquery');
    }
}
```

Or override the strategy per contain path:

```php
$users = $Users->find()
    ->contain([
        'Posts' => [
            'strategy' => 'cte',
        ],
    ])
    ->toArray();
```

## Filtering with relationship joins

When you need join semantics for filtering (and don’t necessarily want relationship properties hydrated), use the relationship-aware join helpers on `SelectQuery`.

### `leftJoinWith()` and `innerJoinWith()`

Use these to add relationship joins to the query.

```php
$query = $Users->find()
    ->leftJoinWith('Posts', ['Posts.published' => true]);
```

### `matching()`

`matching()` behaves like an `INNER` join and also hydrates matching rows into `_matchingData` on the entity.

```php
$users = $Users->find()
    ->matching('Posts', ['Posts.published' => true])
    ->toArray();

$matching = $users[0]->get('_matchingData');
```

### `notMatching()`

`notMatching()` excludes rows that have matching related records by adding a `NOT EXISTS (...)` subquery.

```php
$users = $Users->find()
    ->notMatching('Posts', ['Posts.published' => true])
    ->toArray();
```

For nested resource binding that scopes a child entity lookup through a parent relationship, see [Route Bindings](../routing/route-bindings.md).

## Cascading deletes and unlinking

When you delete an entity with cascading enabled (`delete($entity, $cascade = true, ...)`), the ORM will attempt to unlink rows through owning-side relationships (for example `hasOne`, `hasMany`, `manyToMany`).

Unlink behavior is relationship-aware:

- If the relationship is `dependent`, related rows are deleted.
- If the relationship is not `dependent` and the foreign key column is nullable, related rows are updated by setting the foreign key to `null`.
- If the foreign key column is not nullable, related rows are deleted (even if `dependent` is not enabled).

For `ManyToMany`, unlinking removes rows from the junction model/table; it does not delete the target entities.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Invalid relationship names in `contain()` throw an ORM exception during normalization.
- Relationship properties cannot conflict with real table columns; use `propertyName` to avoid collisions.
- When `strategy` is `join`, only join-safe contain options are allowed (for example, `orderBy`, `limit`, and `callback` are rejected).
- Calling `contain()` multiple times merges contain trees; if both contain calls include a `callback`, they are composed (the earlier callback runs first).
- `matching()` hydrates matching rows into `_matchingData`; `ManyToMany` attaches junction row data to each related entity under `_joinData`.

## Related

- [Models](models.md)
- [Entities](entities.md)
- [Finding Data](finding.md)
- [Saving Data](saving.md)
- [Deleting Data](deleting.md)
