# Schema

Use `Schema` to inspect the tables, columns, indexes, and foreign keys already present in a database.

Schema is read-oriented. Use [Forge](forge.md) or [database migrations](migrations.md) to create or modify database structure.

## Table of Contents

- [Resolve Schema](#resolve-schema)
- [Inspect tables](#inspect-tables)
- [Inspect columns](#inspect-columns)
  - [Read column defaults](#read-column-defaults)
  - [Attach PHP enum metadata](#attach-php-enum-metadata)
- [Inspect indexes and constraints](#inspect-indexes-and-constraints)
- [Refresh schema data](#refresh-schema-data)
- [Driver-specific metadata](#driver-specific-metadata)
- [Schema reference](#schema-reference)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Resolve Schema

Resolve a `Schema` for the connection you want to inspect:

```php
use Fyre\DB\Schema\SchemaRegistry;

$db = db();
$schema = app(SchemaRegistry::class)->use($db);
```

`SchemaRegistry` selects the handler mapped to the connection class and reuses it for later calls with the same connection. Use `map($connectionClass, $schemaClass)` to register a Schema implementation for a custom connection class.

## Inspect tables

Use `tableNames()` to list table names, `hasTable()` to check an optional table, and `table()` to load its metadata:

```php
$tableNames = $schema->tableNames();

if ($schema->hasTable('users')) {
    $users = $schema->table('users');
}
```

`table()` throws an `InvalidArgumentException` when the table does not exist. If you need every table object, `tables()` returns a lazy collection keyed by table name:

```php
foreach ($schema->tables() as $name => $table) {
    $columns = $table->columnNames();
}
```

A schema `Table` exposes its name, comment, owning `Schema`, and normalized metadata through `getName()`, `getComment()`, `getSchema()`, and `toArray()`.

## Inspect columns

Open a table, then check or load columns by name:

```php
$users = $schema->table('users');

if ($users->hasColumn('email')) {
    $email = $users->column('email');
}
```

`column()` throws an `InvalidArgumentException` when the column is missing. Use `columnNames()` for names or `columns()` for a lazy collection of `Column` objects.

Column metadata is available through these accessors:

| Metadata | Method |
| --- | --- |
| name and driver-reported type | `getName()`, `getType()` |
| length and numeric shape | `getLength()`, `getPrecision()`, `getScale()` |
| fractional-second precision | `getFractionalSeconds()` |
| default and comment | `getDefault()`, `getComment()` |
| flags | `isNullable()`, `isUnsigned()`, `isAutoIncrement()` |
| owning table | `getTable()` |
| normalized metadata array | `toArray()` |
| resolved framework type | `type()` |

`type()` maps driver metadata to a `Fyre\DB\Type` instance. Use it when a database value needs to be converted consistently with the rest of the framework:

```php
$column = $schema->table('users')->column('created');
$created = $column->type()->fromDatabase($value);
```

See [Database types](types.md) for type conversion and custom mappings.

### Read column defaults

`getDefault()` returns the normalized schema value. It may be a scalar, `null`, or a `LiteralExpression` when the database reports an expression such as `CURRENT_TIMESTAMP`.

`defaultValue()` returns the corresponding parsed PHP value:

```php
$created = $schema->table('users')->column('created');

$definition = $created->getDefault();
$value = $created->defaultValue();
```

Scalar defaults are parsed through the column's type. An expression default is evaluated with a `SELECT` query, so prefer `getDefault()` or `toArray()` when inspecting many columns and call `defaultValue()` only when the evaluated value is needed.

If the column has no default, `defaultValue()` returns `null` for a nullable column and `''` otherwise.

### Attach PHP enum metadata

Database introspection cannot infer which PHP enum class an application uses for a column. Attach that metadata to the schema table when the column should resolve as a specific enum:

```php
use App\Enums\Status;

$articles = $schema->table('articles');
$articles->setEnumClass('status', Status::class);
```

Use `getEnumClass()`, `hasEnumClass()`, and `clearEnumClass()` on the table to manage the mapping. The corresponding column exposes `getEnumClass()` and `hasEnumClass()`.

## Inspect indexes and constraints

Use `hasIndex()`, `index()`, and `indexes()` to inspect indexes. `primaryKey()` returns the primary-key columns or `null` when no primary key is present:

```php
$users = $schema->table('users');

$primaryKey = $users->primaryKey();

foreach ($users->indexes() as $name => $index) {
    $metadata = [
        'columns' => $index->getColumns(),
        'unique' => $index->isUnique(),
        'primary' => $index->isPrimary(),
        'type' => $index->getType(),
    ];
}
```

Use `hasForeignKey()`, `foreignKey()`, and `foreignKeys()` for foreign-key constraints:

```php
foreach ($users->foreignKeys() as $name => $foreignKey) {
    $metadata = [
        'columns' => $foreignKey->getColumns(),
        'referencedTable' => $foreignKey->getReferencedTable(),
        'referencedColumns' => $foreignKey->getReferencedColumns(),
        'onUpdate' => $foreignKey->getOnUpdate(),
        'onDelete' => $foreignKey->getOnDelete(),
    ];
}
```

`index()` and `foreignKey()` throw an `InvalidArgumentException` when the requested object does not exist. Both metadata objects provide `getName()`, `getTable()`, and `toArray()` in addition to the accessors shown above.

`hasAutoIncrement()` checks whether any column on the table is marked as auto-incrementing.

## Refresh schema data

Schema objects load metadata lazily and may use the `_schema` cache configuration. Clear the appropriate level after database structure changes that were made outside Forge:

| Method | Effect |
| --- | --- |
| `Table::clear()` | discard loaded columns, indexes, and foreign keys for one table |
| `Schema::clear()` | discard all loaded tables and schema cache entries for the connection |

Forge clears affected schema state after it executes DDL. Manual SQL does not, so clear stale schema data yourself before reading it again.

Schema caching is enabled only when `CacheManager` contains a configuration named `_schema`. Without that configuration, metadata is loaded directly from the database.

## Driver-specific metadata

The built-in mappings are:

| Connection | Schema handler |
| --- | --- |
| `MysqlConnection` | `MysqlSchema` |
| `PostgresConnection` | `PostgresSchema` |
| `SqliteConnection` | `SqliteSchema` |

Some handlers expose additional metadata through concrete classes:

- `MysqlTable` provides `getEngine()`, `getCharset()`, and `getCollation()`.
- `MysqlColumn` provides enum or set values through `getValues()`, plus `getCharset()` and `getCollation()`.
- `SqliteIndex` does not expose an index type.
- SQLite cannot read foreign-key constraint names from `PRAGMA foreign_key_list`; generated names use `<table>_<column>_<column>...`.

## Schema reference

The main inspection APIs are:

| API | Purpose |
| --- | --- |
| `SchemaRegistry::use($connection)` | resolve the Schema handler for a connection |
| `SchemaRegistry::map($connectionClass, $schemaClass)` | register a custom handler mapping |
| `Schema::tableNames()` | list discovered table names |
| `Schema::hasTable($name)` | check whether a table exists |
| `Schema::table($name)` | load one table |
| `Schema::tables()` | iterate table objects lazily |
| `Schema::getConnection()` | return the underlying connection |
| `Schema::getDatabaseName()` | return the configured database name or `''` |
| `Table::columnNames()`, `hasColumn()`, `column()`, `columns()` | inspect columns |
| `Table::hasIndex()`, `index()`, `indexes()`, `primaryKey()` | inspect indexes and primary keys |
| `Table::hasForeignKey()`, `foreignKey()`, `foreignKeys()` | inspect foreign-key constraints |
| `Column::type()` | resolve a framework type from column metadata |

## Behavior notes

- Schema introspection lists base tables, not views; SQLite also excludes `sqlite_sequence`.
- Table, column, index, and foreign-key lookup methods throw when the requested object is missing.
- `Column::defaultValue()` can execute a query when the default is a database expression.
- Schema metadata and supported fields vary by driver.
- Manual DDL can leave loaded schema objects stale until `Table::clear()` or `Schema::clear()` is called.

## Related

- [Database](index.md)
- [Database connections](connections.md)
- [Database queries](queries.md)
- [Database types](types.md)
- [Forge](forge.md)
- [Database migrations](migrations.md)
