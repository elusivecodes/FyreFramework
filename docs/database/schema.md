# Schema

Use `Schema` when you need to inspect an existing database: list tables, read columns, or check indexes and foreign keys.

Use [Forge](forge.md) or [Database Migrations](migrations.md) when you need to change structure instead of read it.

## Table of Contents

- [Start here](#start-here)
- [Working with schema objects](#working-with-schema-objects)
  - [Performance note: parsing default values](#performance-note-parsing-default-values)
- [Driver-specific metadata](#driver-specific-metadata)
  - [Built-in schema handlers](#built-in-schema-handlers)
  - [Extra metadata](#extra-metadata)
- [Method guide](#method-guide)
  - [`SchemaRegistry`](#schemaregistry)
  - [`Schema`](#schema-1)
  - [`Table`](#table)
  - [`Column`](#column)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Resolve a `Schema` from a connection, then open the tables you want to inspect.

```php
use Fyre\DB\Schema\SchemaRegistry;

$db = db();
$schema = app(SchemaRegistry::class)->use($db);
```

Common tasks:

- call `hasTable()` before working with an optional table
- open a table with `table('users')`
- inspect columns, indexes, or foreign keys from the returned `Table`

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

`Column::defaultValue()` returns a parsed default value. When the configured default is a database expression (represented as a `QueryLiteral`), this method executes a `SELECT` query to evaluate it. Scalar defaults are parsed through the column type without querying.

If you are inspecting lots of columns (for example across many tables), prefer reading the normalized default via `Column::getDefault()` (or `Column::toArray()`) and only call `defaultValue()` for the specific columns you need.

```php
$users = $schema->table('users');
$created = $users->column('created');

$normalizedDefault = $created->getDefault();
$parsedDefault = $created->defaultValue(); // may execute a query
```

## Driver-specific metadata

### Built-in schema handlers

Schema introspection is implemented by a handler matched to your connection type. The built-in mappings are:

- `MysqlConnection` → `MysqlSchema`
- `PostgresConnection` → `PostgresSchema`
- `SqliteConnection` → `SqliteSchema`

When you need to register a custom handler for your own connection class, use `SchemaRegistry::map()`.

### Extra metadata

Some schema metadata is only available on certain drivers via driver-specific subclasses:

- MySQL tables use `MysqlTable` (engine/charset/collation via `getEngine()`, `getCharset()`, and `getCollation()`).
- MySQL columns use `MysqlColumn` (enum/set values via `getValues()`, plus `getCharset()` / `getCollation()`).
- SQLite indexes use `SqliteIndex`, which does not expose an index type.

## Method guide

### `SchemaRegistry`

#### **Map a connection class to a schema handler** (`map()`)

Registers the `Schema` implementation to use for a given `Connection` class.

Arguments:
- `$connectionClass` (`class-string<Connection>`): the connection class name.
- `$schemaClass` (`class-string<Schema>`): the schema class name (must extend `Schema`).

```php
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\Handlers\Mysql\MysqlSchema;

$schemaRegistry->map(MysqlConnection::class, MysqlSchema::class);
```

#### **Get a Schema for a connection** (`use()`)

Returns the `Schema` instance for the provided connection object.

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

#### **Clear loaded schema data** (`clear()`)

Clears loaded table data and any schema cache entries for this schema.

```php
$schema->clear();
```

#### **Get the connection or database name** (`getConnection()`, `getDatabaseName()`)

- `getConnection()` returns the `Connection` backing this schema.
- `getDatabaseName()` returns the configured database name (or `''`).

### `Table`

#### **Get metadata** (`getName()`, `getComment()`, `getSchema()`, `toArray()`)

- `getName()` returns the table name.
- `getComment()` returns the table comment (or `null` when unavailable).
- `getSchema()` returns the owning `Schema`.
- `toArray()` returns table metadata as an array (driver-specific tables may include additional keys).

#### **Clear loaded table data** (`clear()`)

Clears loaded column, index, and foreign-key data for the table. If schema caching is enabled, it also clears cached entries for that table.

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

- If the introspected default is a scalar, it is parsed through the column type.
- If the introspected default is a `QueryLiteral` (database expression), this method executes a `SELECT` query to evaluate it.
- If there is no default, this method returns `null` for nullable columns and `''` otherwise.

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

## Behavior notes

A few behaviors are worth keeping in mind:

- Schema caching is only used when `CacheManager` has a config named `_schema`; otherwise reads go straight to the database.
- `Schema::clear()` clears loaded table data and any schema cache entries for that connection.
- Schema introspection lists tables only, not views. `Schema::tableNames()` reflects what each driver exposes as a base table, and SQLite also excludes `sqlite_sequence`.
- `Column::defaultValue()` may execute a `SELECT` query to evaluate expression defaults such as `CURRENT_TIMESTAMP`, which can matter if you call it in a tight loop.
- SQLite does not expose foreign key constraint names via `PRAGMA foreign_key_list`, so generated names follow the form `<table>_<column>_<column>...`.

## Related

- [Database](index.md)
- [Database connections](connections.md)
- [Database queries](queries.md)
- [Database types](types.md)
- [Forge](forge.md)
- [Database Migrations](migrations.md)
