# Forge

Use `Forge` to create and change database tables from PHP. It provides driver-aware DDL operations for columns, indexes, constraints, and table metadata.

Most applications use Forge inside [database migrations](migrations.md), but the same API can be used by setup scripts and other tooling.

## Table of Contents

- [Resolve Forge](#resolve-forge)
- [Create a table](#create-a-table)
- [Modify a table](#modify-a-table)
  - [Define columns](#define-columns)
  - [Add indexes](#add-indexes)
  - [Add foreign keys](#add-foreign-keys)
- [Rename or remove schema objects](#rename-or-remove-schema-objects)
- [Preview generated SQL](#preview-generated-sql)
- [Driver differences](#driver-differences)
- [Forge reference](#forge-reference)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Resolve Forge

Resolve a `Forge` for the connection you want to modify:

```php
use Fyre\DB\Forge\ForgeRegistry;

$db = db();
$forge = app(ForgeRegistry::class)->use($db);
```

`ForgeRegistry` selects the handler mapped to the connection class and reuses it for later calls with the same connection. Use `map($connectionClass, $forgeClass)` to register a Forge implementation for a custom connection class.

## Create a table

Use `createTable()` when the table can be described with column, index, and foreign-key arrays:

```php
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

$forge->createTable(
    'roles',
    [
        'id' => [
            'type' => IntegerType::class,
            'autoIncrement' => true,
        ],
        'name' => [
            'type' => StringType::class,
            'length' => 100,
        ],
    ],
    [
        'primary' => [
            'columns' => 'id',
            'primary' => true,
        ],
        'name' => [
            'unique' => true,
        ],
    ]
);
```

For a fluent definition, call `build()` and queue the same operations on the returned `Table`:

```php
$forge->build('roles')
    ->addColumn('id', [
        'type' => IntegerType::class,
        'autoIncrement' => true,
    ])
    ->addColumn('name', [
        'type' => StringType::class,
        'length' => 100,
    ])
    ->setPrimaryKey('id')
    ->addIndex('name', [
        'unique' => true,
    ])
    ->execute();
```

`createTable()` executes immediately. A built `Table` waits until `execute()` is called.

## Modify a table

`build($name)` loads an existing table's current definition, allowing several changes to be queued and executed together:

```php
use Fyre\DB\Types\IntegerType;

$forge->build('roles')
    ->addColumn('description', [
        'nullable' => true,
    ])
    ->addColumn('user_id', [
        'type' => IntegerType::class,
    ])
    ->addIndex('description')
    ->addForeignKey('fk_roles_user_id', [
        'columns' => 'user_id',
        'referencedTable' => 'users',
        'referencedColumns' => 'id',
        'onDelete' => 'cascade',
    ])
    ->execute();
```

For a single change, the corresponding method on `Forge` builds and executes the operation immediately:

```php
$forge->addColumn('roles', 'description', [
    'nullable' => true,
]);

$forge->addIndex('roles', 'description');
```

Use the fluent form when several changes belong together. Forge runs the generated statements in order, but does not automatically wrap them in a transaction.

### Define columns

Use `addColumn()`, `changeColumn()`, and `dropColumn()` to manage columns. `changeColumn()` also accepts a `name` option to rename the column.

Common column options are:

| Option | Type | Purpose |
| --- | --- | --- |
| `type` | `class-string<Type>\|string` | portable type handler class or driver-specific type name |
| `length` | `int\|null` | maximum length where supported |
| `precision` | `int\|null` | numeric precision |
| `scale` | `int\|null` | numeric scale |
| `fractionalSeconds` | `int\|null` | fractional-second precision |
| `nullable` | `bool` | allow `NULL` values |
| `unsigned` | `bool` | use an unsigned numeric type where supported |
| `default` | `bool\|float\|int\|string\|LiteralExpression\|null` | scalar default, SQL expression, or no default |
| `comment` | `string\|null` | column comment where supported |
| `autoIncrement` | `bool` | use the driver's auto-increment behavior |

Prefer type classes from `Fyre\DB\Types` for portable definitions. Use a driver type string only when the schema deliberately depends on that database.

Scalar defaults are quoted by the driver. Wrap a deliberate SQL expression in `LiteralExpression`; `null` means no `DEFAULT` clause rather than `DEFAULT NULL`.

```php
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\TimeType;

$forge->build('events')
    ->addColumn('event_date', [
        'type' => DateType::class,
    ])
    ->addColumn('created_at', [
        'type' => DateTimeType::class,
        'default' => new LiteralExpression('CURRENT_TIMESTAMP'),
    ])
    ->addColumn('start_time', [
        'type' => TimeType::class,
        'fractionalSeconds' => 3,
    ])
    ->execute();
```

These portable classes map to the corresponding driver type family:

| Type class | MySQL/MariaDB | PostgreSQL | SQLite |
| --- | --- | --- | --- |
| `DateType` | `DATE` | `DATE` | `DATE` |
| `DateTimeType` | `DATETIME` | `TIMESTAMP WITHOUT TIME ZONE` | `DATETIME` |
| `TimeType` | `TIME` | `TIME WITHOUT TIME ZONE` | `TIME` |

The generated SQL can include a precision suffix. Set `fractionalSeconds` to `3` on a `TimeType` column when it must retain the millisecond precision of framework `Time` values.

When `changeColumn()` changes the column type, its previous `length` and `precision` are cleared unless replacements are supplied.

### Add indexes

Use `addIndex()` and `dropIndex()` to manage indexes. A built `Table` accepts these options:

| Option | Type | Purpose |
| --- | --- | --- |
| `columns` | `string\|string[]` | columns in the index; defaults to the index name |
| `unique` | `bool` | create a unique index |
| `primary` | `bool` | create a primary key and imply `unique` |
| `type` | `string\|null` | driver-specific index type, normalized to lowercase |

```php
$forge->build('users')
    ->addIndex('primary', [
        'columns' => 'id',
        'primary' => true,
    ])
    ->addIndex('email', [
        'unique' => true,
    ])
    ->execute();
```

If `columns` is omitted, Forge uses the index name. Name a single-column index after its column or pass `columns` explicitly.

### Add foreign keys

Use `addForeignKey()` and `dropForeignKey()` to manage foreign-key constraints:

| Option | Type | Purpose |
| --- | --- | --- |
| `columns` | `string\|string[]` | local columns; defaults to the foreign-key name |
| `referencedTable` | `string` | referenced table |
| `referencedColumns` | `string\|string[]` | referenced columns |
| `onUpdate` | `string\|null` | update action |
| `onDelete` | `string\|null` | delete action |

```php
$forge->addForeignKey('posts', 'fk_posts_user_id', [
    'columns' => 'user_id',
    'referencedTable' => 'users',
    'referencedColumns' => 'id',
    'onDelete' => 'cascade',
]);
```

Avoid giving an index and foreign key the same name on one table. Dropping either removes same-named entries from the pending definition.

## Rename or remove schema objects

The `Forge` convenience methods execute these changes immediately:

| Task | Method |
| --- | --- |
| rename a column | `renameColumn($tableName, $columnName, $newColumnName)` |
| rename a table | `renameTable($tableName, $newTableName)` |
| remove a column | `dropColumn($tableName, $columnName)` |
| remove an index | `dropIndex($tableName, $indexName)` |
| remove a foreign key | `dropForeignKey($tableName, $foreignKeyName)` |
| remove a table | `dropTable($tableName)` |

On a built `Table`, use `changeColumn($name, ['name' => $newName])`, `rename()`, `dropColumn()`, `dropIndex()`, `dropForeignKey()`, or `drop()`, then call `execute()`.

## Preview generated SQL

Call `sql()` on a built `Table` to return the driver-specific statements without executing them:

```php
$table = $forge->build('roles')
    ->addColumn('description', [
        'nullable' => true,
    ])
    ->addIndex('description');

$queries = $table->sql();
```

This previews Forge DDL only. A migration dry run lists migration names and directions because a migration can run arbitrary PHP and SQL outside Forge; see [Preview with a dry run](migrations.md#preview-with-a-dry-run).

## Driver differences

The built-in mappings are:

| Connection | Forge handler |
| --- | --- |
| `MysqlConnection` | `MysqlForge` |
| `PostgresConnection` | `PostgresForge` |
| `SqliteConnection` | `SqliteForge` |

Driver-specific behavior includes:

- MySQL and PostgreSQL handlers provide `createSchema()`, `dropSchema()`, and `dropPrimaryKey()`.
- MySQL table options include `engine`, `charset`, `collation`, and `comment`.
- MySQL columns accept `first` and `after` when adding or changing columns.
- MySQL `enum` and `set` columns accept either explicit `values` or a PHP enum class.
- PostgreSQL represents primary keys and unique indexes as table constraints and requires `btree` for those constraints.
- PostgreSQL and SQLite reject unsupported enum and set column types.
- SQLite cannot modify columns, add or remove foreign keys on existing tables, or add or remove primary keys.

Use a concrete-handler check before calling a driver-only method:

```php
use Fyre\DB\Forge\Handlers\Mysql\MysqlForge;

if ($forge instanceof MysqlForge) {
    $forge->createSchema('analytics');
}
```

## Forge reference

The main APIs are grouped by how they execute:

| API | Behavior |
| --- | --- |
| `ForgeRegistry::use($connection)` | resolve the Forge handler for a connection |
| `ForgeRegistry::map($connectionClass, $forgeClass)` | register a custom handler mapping |
| `Forge::build($tableName, $options = [])` | return a table definition without executing it |
| `Forge::createTable(...)` | create a table immediately from definition arrays |
| `Forge::addColumn()`, `changeColumn()`, `renameColumn()`, `dropColumn()` | build and execute one column change immediately |
| `Forge::addIndex()`, `dropIndex()` | build and execute one index change immediately |
| `Forge::addForeignKey()`, `dropForeignKey()` | build and execute one foreign-key change immediately |
| `Forge::renameTable()`, `dropTable()` | rename or remove a table immediately |
| `Forge::alterTable($tableName, $options = [])` | apply table options immediately |
| `Forge::getConnection()` | return the underlying connection |
| `Table::addColumn()`, `changeColumn()`, `dropColumn()` | queue column changes |
| `Table::addIndex()`, `dropIndex()` | queue index changes |
| `Table::addForeignKey()`, `dropForeignKey()` | queue foreign-key changes |
| `Table::rename()`, `drop()` | queue a table rename or removal |
| `Table::setPrimaryKey($columns)` | queue the driver-appropriate primary key |
| `Table::sql()` | return queued DDL statements without execution |
| `Table::execute()` | execute queued statements and refresh affected schema state |
| `Table::columns()`, `indexes()`, `foreignKeys()` | inspect the current in-memory definition |
| `Table::toArray()` | return table metadata such as name, comment, and driver options |

## Behavior notes

- Forge convenience methods execute immediately; a built `Table` does not execute until `execute()` is called.
- `Table::execute()` returns without clearing schema state when there are no generated statements.
- Generated statements run sequentially and are not automatically wrapped in a transaction.
- DDL output and feature support vary by driver.
- Operations fail when they add an object that already exists or remove one that is missing.
- Schema state is refreshed after an executed change, rename, or drop.

## Related

- [Database connections](connections.md)
- [Schema](schema.md)
- [Database migrations](migrations.md)
- [Database types](types.md)
