# Schema

Schema reads the structure of an existing database (introspection): tables, columns, indexes, and foreign keys. Schema objects are resolved per connection type, so the same code can work across supported drivers.

See [Database connections](connections.md) for connection setup, [Forge](forge.md) for schema modification (DDL), and [Database Migrations](migrations.md) for applying schema changes over time.

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Getting a Schema instance](#getting-a-schema-instance)
- [Working with schema objects](#working-with-schema-objects)
  - [Performance note: parsing default values](#performance-note-parsing-default-values)
- [Driver-specific handlers](#driver-specific-handlers)
  - [Driver-specific metadata](#driver-specific-metadata)
- [Method guide](#method-guide)
  - [`SchemaRegistry`](#schemaregistry)
  - [`Schema`](#schema-1)
  - [`Table`](#table)
  - [`Column`](#column)
  - [`Index`](#index)
  - [`ForeignKey`](#foreignkey)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use Schema when code needs to *observe* database structure:

- Detect whether a table exists.
- List tables and iterate table definitions.
- Inspect columns, indexes, and foreign keys for a table.

For applying schema changes, use [Forge](forge.md) (DDL) or [Database Migrations](migrations.md) (repeatable DDL over time).

## Mental model

`SchemaRegistry` resolves a database-specific `Schema` implementation for a `Connection` and caches that `Schema` per connection object using a `WeakMap`.

- `Schema` provides access to tables and a shared caching mechanism for introspection reads.
- `Table` provides access to a table’s columns, indexes, and foreign keys (loaded lazily).
- `Column`, `Index`, and `ForeignKey` represent metadata objects built from introspection results.

## Getting a Schema instance

Use `SchemaRegistry::use()` to resolve a `Schema` for a connection.

Most examples on this page assume you already have a `$schema` (`Schema`) instance.

Resolve it directly from the registry and a connection:

```php
use Fyre\DB\ConnectionManager;
use Fyre\DB\Schema\SchemaRegistry;

$connection = app(ConnectionManager::class)->use();
$schema = app(SchemaRegistry::class)->use($connection);
```

If helpers are available, you can resolve the connection part more tersely:

```php
use Fyre\DB\Schema\SchemaRegistry;

$schema = app(SchemaRegistry::class)->use(db());
```

## Working with schema objects

`Schema` is table-centric: open tables by name, then inspect their columns/indexes/foreign keys.

Example: open a table and read its primary key and columns

```php
$users = $schema->table('users');

$primaryKey = $users->primaryKey();
$columnNames = $users->columnNames();
```

Example: safely check before opening

```php
if ($schema->hasTable('users')) {
    $users = $schema->table('users');

    if ($users->hasColumn('email')) {
        $emailMeta = $users->column('email')->toArray();
    }
}
```

Example: inspect indexes and foreign keys

```php
$users = $schema->table('users');

$indexInfo = [];
foreach ($users->indexes() as $name => $index) {
    $indexInfo[$name] = [
        'primary' => $index->isPrimary(),
        'unique' => $index->isUnique(),
        'columns' => $index->getColumns(),
        'type' => $index->getType(),
    ];
}

$foreignKeyInfo = [];
foreach ($users->foreignKeys() as $name => $foreignKey) {
    $foreignKeyInfo[$name] = [
        'columns' => $foreignKey->getColumns(),
        'referencedTable' => $foreignKey->getReferencedTable(),
        'referencedColumns' => $foreignKey->getReferencedColumns(),
        'onUpdate' => $foreignKey->getOnUpdate(),
        'onDelete' => $foreignKey->getOnDelete(),
    ];
}
```

Example: iterate all tables lazily

```php
foreach ($schema->tables() as $name => $table) {
    // $table is a driver-specific Table implementation.
}
```

### Performance note: parsing default values

`Column::defaultValue()` returns a parsed default value. When the configured default is a database expression (represented as a `QueryLiteral`), this method executes a `SELECT` query to evaluate it. Scalar defaults are returned directly without querying.

If you are inspecting lots of columns (for example across many tables), prefer reading the normalized default via `Column::getDefault()` (or `Column::toArray()`) and only call `defaultValue()` for the specific columns you need.

```php
$users = $schema->table('users');
$created = $users->column('created');

$normalizedDefault = $created->getDefault();
$parsedDefault = $created->defaultValue(); // may execute a query
```

## Driver-specific handlers

Schema introspection is implemented by driver-specific `Schema` classes. `SchemaRegistry` ships with default mappings for the built-in connection handlers:

- `MysqlConnection` → `MysqlSchema`
- `PostgresConnection` → `PostgresSchema`
- `SqliteConnection` → `SqliteSchema`

The objects returned at runtime are typically driver-specific subclasses (for example, MySQL tables/columns include extra metadata like engine/charset/collation and enum values). When you need to register your own mapping, use `SchemaRegistry::map()`.

### Driver-specific metadata

Some schema metadata is only available on certain drivers via driver-specific subclasses:

- MySQL tables use `MysqlTable` (engine/charset/collation via `getEngine()`, `getCharset()`, and `getCollation()`).
- MySQL columns use `MysqlColumn` (enum/set values via `getValues()`, plus `getCharset()` / `getCollation()`).
- SQLite indexes use `SqliteIndex`, which does not expose an index type.

## Method guide

### `SchemaRegistry`

#### **Map a connection class to a schema handler** (`map()`)

Registers the `Schema` implementation to use for a given `Connection` class.

The mapping itself is stored immediately. Validation that the schema class extends `Schema` happens later when a connection is resolved through `use()`.

Arguments:
- `$connectionClass` (`class-string<Connection>`): the connection class name.
- `$schemaClass` (`class-string<Schema>`): the schema class name (must extend `Schema`).

```php
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\Handlers\Mysql\MysqlSchema;

$schemaRegistry->map(MysqlConnection::class, MysqlSchema::class);
```

#### **Get a shared Schema for a connection** (`use()`)

Returns a shared `Schema` instance for the provided connection object (cached internally with a `WeakMap`).

If the exact connection class is not mapped, `SchemaRegistry` will look through parent connection classes until it finds a mapped schema.

Arguments:
- `$connection` (`Connection`): the connection instance.

```php
$schema = $schemaRegistry->use($connection);
```

### `Schema`

`Schema` is the driver-aware introspector API for reading tables, columns, indexes, and foreign keys.

#### **List tables** (`tableNames()`)

Returns the list of table names discovered for the current connection.

```php
$tables = $schema->tableNames();
```

#### **Check whether a table exists** (`hasTable()`)

Arguments:
- `$name` (`string`): the table name.

```php
$ok = $schema->hasTable('users');
```

#### **Open a table** (`table()`)

Returns a `Table` instance for an existing table name.

Arguments:
- `$name` (`string`): the table name.

Throws:
- `InvalidArgumentException` if the table does not exist.

```php
$table = $schema->table('users');
```

#### **Iterate tables lazily** (`tables()`)

Returns a lazy `Collection<string, Table>` that yields `Table` objects on iteration.

```php
foreach ($schema->tables() as $name => $table) {
    $name;
    $table;
}
```

#### **Clear loaded state** (`clear()`)

Clears in-memory table data and deletes the cached tables list when schema caching is enabled.

```php
$schema->clear();
```

#### **Access connection and cache information** (`getConnection()`, `getDatabaseName()`, `getCache()`, `getCachePrefix()`)

- `getConnection()` returns the `Connection` backing this schema.
- `getDatabaseName()` returns the configured database name (or `''`).
- `getCache()` returns the configured `Cacher` when a cache config named `_schema` exists, otherwise `null`.
- `getCachePrefix()` returns the cache prefix derived from connection config (`cacheKeyPrefix` + `database`, with `:` replaced by `_`).

### `Table`

#### **Get metadata** (`getName()`, `getComment()`, `getSchema()`, `toArray()`)

- `getName()` returns the table name.
- `getComment()` returns the table comment (or `null` when unavailable).
- `getSchema()` returns the owning `Schema`.
- `toArray()` returns table metadata as an array (driver-specific tables may include additional keys).

#### **Clear loaded state** (`clear()`)

Clears in-memory column/index/foreign key data for the table. If schema caching is enabled, it also deletes the cached table-specific keys `<table>.columns`, `<table>.indexes`, and `<table>.foreign_keys` under the schema cache prefix.

```php
$table = $schema->table('users');
$table->clear();
```

#### **Work with columns** (`columnNames()`, `hasColumn()`, `column()`, `columns()`)

- `columnNames()` returns the column names.
- `hasColumn($name)` checks for a column by name.
- `column($name)` returns a `Column` object for an existing column name.
- `columns()` returns a lazy `Collection<string, Column>`.

Throws:
- `InvalidArgumentException` if `column($name)` is called for a missing column.

#### **Attach a PHP enum class to a column** (`setEnumClass()`, `getEnumClass()`, `hasEnumClass()`, `clearEnumClass()`)

Schema introspection can be overlaid with framework enum metadata when a column should hydrate and marshal as a PHP enum.

```php
use App\Enums\Status;

$table = $schema->table('articles');
$table->setEnumClass('status', Status::class);
```

#### **Work with indexes** (`hasIndex()`, `index()`, `indexes()`, `primaryKey()`)

- `hasIndex($name)` checks for an index by name.
- `index($name)` returns an `Index` object for an existing index name.
- `indexes()` returns a lazy `Collection<string, Index>`.
- `primaryKey()` returns the primary key columns (or `null` when there is no primary key).

Throws:
- `InvalidArgumentException` if `index($name)` is called for a missing index.

#### **Work with foreign keys** (`hasForeignKey()`, `foreignKey()`, `foreignKeys()`)

- `hasForeignKey($name)` checks for a foreign key by name.
- `foreignKey($name)` returns a `ForeignKey` object for an existing foreign key name.
- `foreignKeys()` returns a lazy `Collection<string, ForeignKey>`.

Throws:
- `InvalidArgumentException` if `foreignKey($name)` is called for a missing foreign key.

#### **Convenience checks** (`hasAutoIncrement()`)

Returns whether any column in the table is marked as auto-increment.

```php
$table = $schema->table('users');
$ok = $table->hasAutoIncrement();
```

### `Column`

#### **Get metadata** (`getName()`, `getType()`, `getLength()`, `getPrecision()`, `getDefault()`, `getComment()`, `getTable()`, `getEnumClass()`, `toArray()`)

- `getType()` returns the driver-reported type string.
- `getDefault()` returns the normalized default value as either a scalar (`string|int|float|bool|null`) or a `QueryLiteral` when the default is a database expression.
- `getEnumClass()` returns the configured PHP enum class when one has been attached to the column.
- `toArray()` returns column metadata as an array (driver-specific columns may include additional keys).

#### **Check flags** (`isNullable()`, `isUnsigned()`, `isAutoIncrement()`)

#### **Parse the default value** (`defaultValue()`)

Returns a parsed default value.

- If the introspected default is a scalar, it is returned as-is.
- If the introspected default is a `QueryLiteral` (database expression), this method executes a `SELECT` query to evaluate it.

```php
$table = $schema->table('users');
$column = $table->column('created_at');

$default = $column->defaultValue();
```

#### **Resolve the framework Type** (`type()`)

Returns a `Type` instance resolved via the driver’s type map and the `TypeParser`. MySQL-style `tinyint(1)` columns are treated as booleans.

See [Database types](types.md) for the built-in type system and casting behavior.

```php
$table = $schema->table('users');
$column = $table->column('id');

$type = $column->type();
```

### `Index`

#### **Get metadata** (`getName()`, `getColumns()`, `getType()`, `getTable()`, `toArray()`)

#### **Check flags** (`isUnique()`, `isPrimary()`)

### `ForeignKey`

#### **Get metadata** (`getName()`, `getColumns()`, `getReferencedTable()`, `getReferencedColumns()`, `getOnUpdate()`, `getOnDelete()`, `getTable()`, `toArray()`)

## Behavior notes

A few behaviors are worth keeping in mind:

- Schema caching is enabled only when `CacheManager` has a config named `_schema`; otherwise all reads are uncached.
- Cache keys are namespaced by `Schema::getCachePrefix()` and the introspection area being cached (for example `tables`, `<table>.columns`, `<table>.indexes`, and `<table>.foreign_keys`).
- `Schema::clear()` loads the current table list, clears in-memory state, and deletes the cached `tables` list plus cached per-table keys (like `<table>.columns`, `<table>.indexes`, and `<table>.foreign_keys`).
- Schema introspection lists tables only (not views). `Schema::tableNames()` reflects what each driver exposes as a “base table” (and SQLite also excludes `sqlite_sequence`).
- `Column::defaultValue()` may execute a `SELECT` query to evaluate expression defaults (for example `CURRENT_TIMESTAMP`), which can matter if you call it in a tight loop.
- SQLite does not expose foreign key constraint names via `PRAGMA foreign_key_list`, so the SQLite handler generates names in the form `<table>_<column>_<column>...`.
- `SchemaRegistry` selects a handler by the connection’s class name, walking up the inheritance chain until a mapping is found; `SchemaRegistry::map()` also normalizes class names by trimming a leading `\`.

## Related

- [Database](index.md)
- [Database connections](connections.md)
- [Database queries](queries.md)
- [Database types](types.md)
- [Forge](forge.md)
- [Database Migrations](migrations.md)
