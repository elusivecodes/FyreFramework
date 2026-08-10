# Forge

Use `Forge` when you want to create or change database tables from PHP code.

Most applications use it inside migrations, but it also works well for setup scripts and tooling.

## Table of Contents

- [Start here](#start-here)
- [Working with DDL operations](#working-with-ddl-operations)
  - [Creating tables](#creating-tables)
  - [Altering existing tables](#altering-existing-tables)
  - [Dropping and renaming](#dropping-and-renaming)
  - [Previewing generated SQL](#previewing-generated-sql)
  - [Naming conventions](#naming-conventions)
- [Driver differences](#driver-differences)
  - [Built-in forge handlers](#built-in-forge-handlers)
  - [Driver-specific features](#driver-specific-features)
- [Method guide](#method-guide)
  - [`ForgeRegistry`](#forgeregistry)
  - [`Forge`](#forge-1)
  - [`Table`](#table)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Resolve a `Forge` from a connection, then either run a one-off change or build a table definition and execute it.

```php
use Fyre\DB\Forge\ForgeRegistry;

$db = db();
$forge = app(ForgeRegistry::class)->use($db);
```

Use Forge when you want to:

- create tables and indexes from code
- batch several schema changes together before running them
- preview generated SQL before you execute it

## Working with DDL operations

Forge supports two styles:

- **Convenience methods** on `Forge` execute immediately and return the same `Forge` instance.
- **Queued operations** on `Table` let you build up multiple changes, then call `execute()` once.

### Creating tables

`Forge::createTable()` is the simplest way to create a table from arrays of columns, indexes, and foreign keys.

```php
$forge->createTable(
    'roles',
    [
        'name' => ['length' => 100],
    ],
    [
        'name' => ['unique' => true],
    ]
);
```

For more control, build a table definition and call `execute()`:

```php
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

$forge->build('roles')
    ->addColumn('id', ['type' => IntegerType::class, 'autoIncrement' => true])
    ->addColumn('name', ['type' => StringType::class, 'length' => 100])
    ->setPrimaryKey('id')
    ->addIndex('name', ['unique' => true])
    ->execute();
```

### Altering existing tables

When `build($name)` targets an existing table, `Table` starts from the current introspected definition and you apply changes relative to it.

Example: batch multiple changes

```php
use Fyre\DB\Types\IntegerType;

$forge->build('roles')
    ->addColumn('description', ['nullable' => true])
    ->addColumn('user_id', ['type' => IntegerType::class])
    ->addIndex('description')
    ->addForeignKey('fk_roles_user_id', [
        'columns' => 'user_id',
        'referencedTable' => 'users',
        'referencedColumns' => 'id',
        'onDelete' => 'cascade',
    ])
    ->execute();
```

Alternative: use convenience methods instead of batching with `build()` (each call executes immediately)

```php
$forge->addColumn('roles', 'description', ['nullable' => true]);
$forge->addIndex('roles', 'description');
```

### Dropping and renaming

Use `dropColumn()`, `dropIndex()`, `dropForeignKey()`, and `dropTable()` to remove schema objects.

For renames:

- `Forge::renameColumn()` is a convenience wrapper around `changeColumn()` with a `name` option.
- `Forge::renameTable()` delegates to `Table::rename()` and executes.

```php
$forge->renameColumn('roles', 'name', 'title');
$forge->renameTable('roles', 'app_roles');
```

### Previewing generated SQL

If you want to inspect what will run (for example while authoring migrations), build a `Table` operation and call `sql()` before executing:

```php
$table = $forge->build('roles')
    ->addColumn('description', ['nullable' => true])
    ->addIndex('description');

$queries = $table->sql();
```

`sql()` returns an array of driver-specific DDL queries and does not execute them. The generated SQL can differ significantly between drivers for the same high-level table change.

### Naming conventions

Forge uses object names as defaults in a few places, so consistent naming avoids surprises:

- `Table::addIndex($name, $options)` defaults `columns` to `$name` when `columns` is omitted.
- `Table::addForeignKey($name, $options)` defaults `columns` to `$name` when `columns` is omitted.

In practice, either:

- name the index/foreign key after the column it targets, **or**
- always pass an explicit `columns` option.

Also avoid reusing the same name for an index and a foreign key on the same table: `dropIndex()` and `dropForeignKey()` will also remove same-named objects from the in-memory definition.

## Driver differences

### Built-in forge handlers

Forge uses a handler matched to your connection type. The built-in mappings are:

- `MysqlConnection` → `MysqlForge`
- `PostgresConnection` → `PostgresForge`
- `SqliteConnection` → `SqliteForge`

### Driver-specific features

Some features are only present on specific driver implementations:

- MySQL and PostgreSQL `Forge` handlers add `createSchema()`, `dropSchema()`, and `dropPrimaryKey()`.
- For MySQL native `enum` and `set` columns, `values` can be either an explicit value list or a PHP enum class name. When a class name is used, MySQL derives the option values from the enum cases.
- SQLite’s handler does not add schema-level operations.

If you need a driver-only method, check the concrete handler first:

```php
use Fyre\DB\Forge\Handlers\Mysql\MysqlForge;

if ($forge instanceof MysqlForge) {
    $forge->createSchema('analytics');
}
```

Example: MySQL enum values from a PHP enum class

```php
use App\Enums\Status;
use Fyre\DB\Types\EnumType;

$forge->addColumn('articles', 'status', [
    'type' => EnumType::class,
    'values' => Status::class,
]);
```

### Driver-specific table behavior

- MySQL tables support `engine`, `charset`, and `collation` options when building a table definition (passed to `build()`).
- MySQL columns support `first` and `after` options on `Table::addColumn()` and `Table::changeColumn()` to control column order.
- PostgreSQL treats primary keys and unique indexes as table constraints (not standalone indexes), and requires a `btree` type for those constraints.
- Some type handlers are not supported on all drivers (for example, enum/set types are rejected by the PostgreSQL and SQLite forge column implementations).

## Method guide

### `ForgeRegistry`

#### **Map a connection class to a forge handler** (`map()`)

Registers the `Forge` implementation to use for a given `Connection` class.

Arguments:
- `$connectionClass` (`class-string<Connection>`): the connection class name.
- `$forgeClass` (`class-string<Forge>`): the forge class name (must extend `Forge`).

```php
use Fyre\DB\Forge\Handlers\Mysql\MysqlForge;
use Fyre\DB\Handlers\Mysql\MysqlConnection;

$forgeRegistry->map(MysqlConnection::class, MysqlForge::class);
```

#### **Get a Forge for a connection** (`use()`)

Returns the `Forge` instance for the provided connection object.

Arguments:
- `$connection` (`Connection`): the connection instance.

```php
$forge = $forgeRegistry->use($connection);
```

### `Forge`

`Forge` is the driver-aware DDL builder API for creating and altering tables.

```php
$forge = $forgeRegistry->use($connection);
```

#### **Build a table definition** (`build()`)

Returns a `Table` instance for the specified table name. If the table exists, the `Table` definition is loaded from introspection so changes can be applied relative to the current structure.

Arguments:
- `$name` (`string`): the table name.
- `$options` (`array<string, mixed>`): driver-specific table options (for example, MySQL `engine`, `charset`, `collation`, and `comment`).

```php
$table = $forge->build('users');
$table
    ->addColumn('id', ['type' => 'int'])
    ->execute();
```

#### **Create a table from arrays** (`createTable()`)

Creates a table by adding columns, indexes, and foreign keys to a built `Table` definition, then executing it.

Arguments:
- `$tableName` (`string`): the table name.
- `$columns` (`array<string, array<string, mixed>>`): column definitions keyed by column name.
- `$indexes` (`array<string, array<string, mixed>>`): index definitions keyed by index name.
- `$foreignKeys` (`array<string, array<string, mixed>>`): foreign key definitions keyed by foreign key name.
- `$options` (`array<string, mixed>`): table options passed to `build()`.

```php
$forge->createTable('users', [
    'id' => ['type' => 'int'],
    'email' => ['type' => 'string', 'length' => 255],
], [
    'idx_users_email' => ['columns' => ['email']],
]);
```

#### **Convenience DDL methods (execute immediately)** (`addColumn()`, `changeColumn()`, `renameColumn()`, `dropColumn()`, `addIndex()`, `dropIndex()`, `addForeignKey()`, `dropForeignKey()`, `alterTable()`, `renameTable()`, `dropTable()`)

These methods build a `Table` operation and call `execute()` as part of the method.

Use them for one-off operations; use `build()->...->execute()` to apply multiple changes as a single generated set of queries.

#### **Access the connection** (`getConnection()`)

- `getConnection()` returns the `Connection` backing this `Forge`.

### `Table`

#### **Queue table changes** (`addColumn()`, `changeColumn()`, `dropColumn()`)

Column definitions accept a set of common options (driver handlers may support additional keys):

- `type` (`class-string<Type>|string`) The column type; can be a type handler class (for example `StringType::class`) or a driver-level type string.
- `length` (`int|null`) The column length (when applicable).
- `precision` (`int|null`) The column precision (when applicable).
- `nullable` (`bool`) Whether the column is nullable.
- `unsigned` (`bool`) Whether the column is unsigned (when supported).
- `default` (`bool|float|int|string|LiteralExpression|null`) The column default value.
  - For scalar values, pass the native PHP type (strings are quoted by the driver generator).
  - For SQL expressions, pass a `LiteralExpression` (for example `new LiteralExpression('CURRENT_TIMESTAMP')`).
  - `null` means “no DEFAULT clause” (not `DEFAULT NULL`).
- `comment` (`string|null`) The column comment (when supported).
- `autoIncrement` (`bool`) Whether the column auto-increments (driver-specific behavior).

In general:

- Prefer **type classes** for portable behavior across drivers.
- Use **driver type strings** only when you need a database-specific type or feature (and you can accept driver differences).

#### **Queue index changes** (`addIndex()`, `dropIndex()`)

Index options include:

- `columns` (`string|string[]`) The indexed columns (defaults to the index name when omitted).
- `unique` (`bool`) Whether the index is unique.
- `primary` (`bool`) Whether the index represents a primary key (implies `unique`).
- `type` (`string|null`) Driver-specific index type (normalized to lowercase).

#### **Queue foreign key changes** (`addForeignKey()`, `dropForeignKey()`)

Foreign key options include:

- `columns` (`string|string[]`) Local column names (defaults to the foreign key name when omitted).
- `referencedTable` (`string`) The referenced table name.
- `referencedColumns` (`string|string[]`) The referenced column names.
- `onUpdate` (`string|null`) Update action (normalized by the generator).
- `onDelete` (`string|null`) Delete action (normalized by the generator).

#### **Rename or drop a table** (`rename()`, `drop()`)

- `rename($newName)` queues a table rename.
- `drop()` queues dropping the table (throws if the table does not exist).

#### **Execute and inspect** (`execute()`, `sql()`, `toArray()`, getters)

- `execute()` generates SQL via `sql()`, runs each query using the underlying connection, clears queued operations, and refreshes schema state as needed.
- `sql()` returns the generated SQL queries without executing them (driver-specific output).
- `getName()`, `getComment()`, `columns()`, `indexes()`, and `foreignKeys()` expose the current in-memory definition.

## Behavior notes

A few behaviors are worth keeping in mind:

- Forge runs real DDL against your connection.
- Forge convenience methods execute immediately; use `build()->...->execute()` when you want to batch changes together.
- Column `default` values accept scalars or `LiteralExpression` for raw SQL expressions.
- When `changeColumn()` changes `type`, `length` and `precision` are cleared unless you provide new values.
- Some drivers handle defaults specially. For example, MySQL normalizes some expression defaults.
- `Table::execute()` runs generated queries sequentially and does not automatically wrap them in a transaction.
- SQLite has strict limitations for existing tables: columns cannot be modified, foreign keys cannot be added or dropped, and primary keys cannot be added or dropped.
- Avoid reusing the same name for an index and a foreign key on the same table; drops can affect same-named objects in the pending table definition.

## Related

- [Database connections](connections.md)
- [Schema](schema.md)
- [Database Migrations](migrations.md)
- [Database types](types.md)
