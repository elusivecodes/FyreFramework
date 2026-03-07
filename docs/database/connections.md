# Database connections

Database connections are configured by name and resolved at runtime. A connection key (like `default` or `reporting`) selects which `Fyre\DB\Connection` instance is used for queries, schema tools, and migrations.

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Configuring connections](#configuring-connections)
  - [Base connection options](#base-connection-options)
  - [Example configuration](#example-configuration)
- [Built-in connection handlers](#built-in-connection-handlers)
  - [MySQL](#mysql)
  - [PostgreSQL](#postgresql)
  - [SQLite](#sqlite)
- [Selecting a connection](#selecting-a-connection)
- [Building one-off connections](#building-one-off-connections)
- [Running queries](#running-queries)
- [Troubleshooting](#troubleshooting)
- [Method guide](#method-guide)
  - [`ConnectionManager`](#connectionmanager)
  - [`Connection`](#connection)
  - [`MysqlConnection`](#mysqlconnection)
  - [`PostgresConnection`](#postgresconnection)
  - [`SqliteConnection`](#sqliteconnection)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Connections are a good fit when you need to:

- use different database backends in the same codebase (for example, MySQL for app data and SQLite for local analytics)
- isolate workloads with multiple connections (separate credentials, timeouts, or hosts)
- keep database access code driver-agnostic by selecting a handler via configuration

## Mental model

`Fyre\DB\ConnectionManager` loads connection configurations from [Config](../core/config.md) (the `Database` key) during construction and provides `Fyre\DB\Connection` instances by key.

- Each config entry must specify a `className` that extends `Fyre\DB\Connection` (for example, `MysqlConnection::class`).
- `ConnectionManager::use()` returns one shared connection instance per key.
- `ConnectionManager::build()` creates a new connection instance from options without storing or sharing it.

## Configuring connections

Connection configuration is read from the `Database` key in your config (see [Config](../core/config.md)). Each named connection config is an options array passed to the selected connection handler.

### Base connection options

These options apply to all connection handlers:

- `className` (`class-string<Fyre\DB\Connection>`): the connection class to build.
- `log` (`bool`): whether query logging is enabled for that connection (default: `false`).
  - Queries are logged at `debug` level with the `queries` scope; configure handlers accordingly (see [Logging](../logging/index.md)).

Other options depend on the selected handler.

### Example configuration

```php
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;

return [
    'Database' => [
        'default' => [
            'className' => MysqlConnection::class,
            'host' => '127.0.0.1',
            'username' => 'app',
            'password' => 'secret',
            'database' => 'app',
        ],
        'analytics' => [
            'className' => SqliteConnection::class,
            'database' => 'tmp/analytics.sqlite',
        ],
    ],
];
```

Example: enabling query logging for a connection

```php
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\Log\Handlers\FileLogger;

return [
    'Database' => [
        'default' => [
            'className' => MysqlConnection::class,
            'host' => '127.0.0.1',
            'username' => 'app',
            'password' => 'secret',
            'database' => 'app',
            'log' => true,
        ],
    ],
    'Log' => [
        'default' => [
            'className' => FileLogger::class,
            'path' => 'tmp/logs',
            'levels' => ['debug'],
            'scopes' => ['queries'],
        ],
    ],
];
```

## Built-in connection handlers

The options below are specific to the built-in handlers under `Fyre\DB\Handlers\*`.

### MySQL

Use `MysqlConnection::class` as `className`.

- `host` (`string`): default `127.0.0.1`
- `username` (`string`): default `''`
- `password` (`string`): default `''`
- `database` (`string`): default `''`
- `port` (`int|string`): default `'3306'`
- `charset` (`string`): default `utf8mb4`
- `collation` (`string`): default `utf8mb4_unicode_ci`
- `compress` (`bool`): default `false`
- `persist` (`bool`): default `false`
- `timeout` (`mixed`): default `null`
- `ssl` (`array`): keys `key`, `cert`, `ca`, `capath`, `cipher` (all default `null`)
- `flags` (`array`): PDO driver options to merge into the default options (default `[]`)

### PostgreSQL

Use `PostgresConnection::class` as `className`.

- `host` (`string`): default `127.0.0.1`
- `username` (`string`): default `''`
- `password` (`string`): default `''`
- `database` (`string`): default `''`
- `port` (`int|string`): default `'5432'`
- `charset` (`string`): default `utf8`
- `schema` (`string`): default `public`
- `persist` (`bool`): default `false`
- `timeout` (`mixed`): default `null`
- `flags` (`array`): PDO driver options to merge into the default options (default `[]`)

### SQLite

Use `SqliteConnection::class` as `className`.

- `database` (`string`): default `:memory:`
- `mask` (`int`): default `0644` (applied when creating a new file database)
- `cache` (`string|null`): default `null`
- `mode` (`string|null`): default `null`
- `persist` (`bool`): default `false`
- `flags` (`array`): PDO driver options to merge into the default options (default `[]`)

## Selecting a connection

Use a connection key to select which stored config to use. When no key is provided, `ConnectionManager::DEFAULT` (`default`) is used.

If the key does not exist, `use()` will fail when it tries to build a connection from the missing config.

```php
use Fyre\DB\ConnectionManager;

$connections = app(ConnectionManager::class);

$default = $connections->use();
$analytics = $connections->use('analytics');
```

If the `db()` helper is available, you can resolve connections by key directly:

```php
$default = db();
$analytics = db('analytics');
```

If you use contextual injection, `#[DB('default')]` can resolve a configured connection while the container is building an object or calling a callable; see [Contextual attributes](../core/contextual-attributes.md).

## Building one-off connections

Use `build()` to construct a connection directly from options without storing it under a key (and without sharing it). The options must include a valid `className` that extends `Fyre\DB\Connection`.

```php
use Fyre\DB\Handlers\Postgres\PostgresConnection;

$temp = $connections->build([
    'className' => PostgresConnection::class,
    'host' => '127.0.0.1',
    'username' => 'app',
    'password' => 'secret',
    'database' => 'temp',
]);
```

## Running queries

Once you have a `Connection`, most day-to-day database work is done through query builder objects. Each builder compiles to SQL and executes through the connection (usually via `Query::execute()`).

Common query types:

- **SELECT**: `$db->select()` returns a `SelectQuery` (see [Select queries](queries.md#select-queries)).
- **INSERT**: `$db->insert()` returns an `InsertQuery` (see [Insert queries](queries.md#insert-queries)).
- **UPDATE**: `$db->update()` returns an `UpdateQuery` (see [Update queries](queries.md#update-queries)).
- **DELETE**: `$db->delete()` returns a `DeleteQuery` (see [Delete queries](queries.md#delete-queries)).
- **UPSERT**: `$db->upsert()` returns an `UpsertQuery` (see [Upsert queries](queries.md#upsert-queries)).
- **INSERT FROM SELECT**: `$db->insertFrom()` returns an `InsertFromQuery` (see [Insert-from queries](queries.md#insert-from-queries)).
- **Batch UPDATE**: `$db->updateBatch()` returns an `UpdateBatchQuery` (see [Update-batch queries](queries.md#update-batch-queries)).

For a deeper guide to building and executing queries (including value binding, result handling, and edge cases), see [Database queries](queries.md).

Prefer bound values wherever possible. Query builders bind values by default (via `Query::execute()`), while raw SQL fragments bypass binding:

- Use query builder methods and condition arrays for parameterized values (see [Binding and expressions](queries.md#binding-and-expressions)).
- Use `Connection::literal()` only for safe, deliberate SQL fragments (like functions or column expressions).
- Avoid embedding user input into literals or raw snippets; see [Raw SQL fragments](queries.md#raw-sql-fragments).

## Troubleshooting

Common issues when setting up connections:

- If you get an `InvalidArgumentException` when calling `use()` / `build()`, make sure your connection config includes a valid `className` that extends `Fyre\DB\Connection` (see [Configuring connections](#configuring-connections)).
- If you call `use()` with a key that has not been configured, connection creation will fail because there is no stored config for that key.
- If a connection fails immediately on `use()`, connection handlers call `connect()` during construction, so network/credential/database-name errors surface as soon as you resolve the connection. Double-check `host`, `port`, `username`, `password`, and `database`.
- If SQLite reports “unable to open database file”, verify the directory for your SQLite file exists and is writable by the PHP process. Use an absolute path if your working directory differs between environments.
- If query logging is enabled but nothing is written, `log: true` emits debug-level logs with the `queries` scope. Ensure your logger is configured for `debug` and includes the `queries` scope (see the example under [Example configuration](#example-configuration)).

## Method guide

### `ConnectionManager`

#### **Get a shared connection** (`use()`)

Returns the shared connection instance for a config key. If the connection has not been created yet, it is built from the stored config and cached.

Arguments:
- `$key` (`string`): the connection key (defaults to `default`).

```php
$db = $connections->use();
$reporting = $connections->use('reporting');
```

#### **Build a connection instance** (`build()`)

Builds a new connection instance from an options array (without storing or sharing it). The options must include a valid `className` that extends `Fyre\DB\Connection`.

Arguments:
- `$options` (`array<string, mixed>`): connection options including `className`.

```php
use Fyre\DB\Handlers\Sqlite\SqliteConnection;

$db = $connections->build([
    'className' => SqliteConnection::class,
    'database' => ':memory:',
]);
```

#### **Read stored configuration** (`getConfig()`)

Returns the stored config array. When called with no key, it returns all stored configs.

Arguments:
- `$key` (`string|null`): the connection key, or `null` to return all configs.

```php
$all = $connections->getConfig();
$default = $connections->getConfig('default');
```

#### **Check whether a config exists** (`hasConfig()`)

Returns whether a config key has been registered.

Arguments:
- `$key` (`string`): the connection key (defaults to `default`).

```php
if ($connections->hasConfig('reporting')) {
    $connections->use('reporting');
}
```

#### **Check whether a connection is loaded** (`isLoaded()`)

Returns whether a shared connection instance has been created for a key.

Arguments:
- `$key` (`string`): the connection key (defaults to `default`).

```php
if (!$connections->isLoaded('analytics')) {
    $connections->use('analytics');
}
```

#### **Register a config** (`setConfig()`)

Registers a config key and options array. Throws if the key already exists.

Arguments:
- `$key` (`string`): the connection key to register.
- `$options` (`array<string, mixed>`): connection options including `className`.

```php
use Fyre\DB\Handlers\Sqlite\SqliteConnection;

$connections->setConfig('temp', [
    'className' => SqliteConnection::class,
    'database' => ':memory:',
]);
```

#### **Remove a config and shared instance** (`unload()`)

Removes both the stored config and any shared connection instance for that key.

Arguments:
- `$key` (`string`): the connection key (defaults to `default`).

```php
$connections->unload('analytics');
```

#### **Clear all configs and instances** (`clear()`)

Clears all stored configs and all shared connection instances.

```php
$connections->clear();
```

### `Connection`

This section documents the `Fyre\DB\Connection` APIs you’ll use most often after resolving a connection from `ConnectionManager`.

```php
$db = $connections->use();
// If helpers are loaded, you can also do: $db = db();
```

#### **Create a SELECT query** (`select()`)

Creates a `SelectQuery` for building and executing `SELECT` statements.

Arguments:
- `$fields` (`array<mixed>|string`): the fields to select (defaults to `'*'`).

```php
$result = $db->select(['id', 'email'])
    ->from('users')
    ->where(['active' => 1])
    ->execute();
```

#### **Create an INSERT query** (`insert()`)

Creates an `InsertQuery` for inserting rows.

```php
$db->insert()
    ->into('users')
    ->values(['email' => 'a@example.com'])
    ->execute();
```

#### **Create an INSERT FROM SELECT query** (`insertFrom()`)

Creates an `InsertFromQuery` for inserting rows from another query.

Arguments:
- `$from` (`Closure|QueryLiteral|SelectQuery|string`): the select query (or other supported source) to insert from.
- `$columns` (`string[]`): the target columns.

```php
$from = $db->select(['id', 'email'])
    ->from('users')
    ->where(['active' => 1]);

$db->insertFrom($from, ['id', 'email'])
    ->into('active_users')
    ->execute();
```

#### **Create an UPDATE query** (`update()`)

Creates an `UpdateQuery` for updating rows.

Arguments:
- `$table` (`array|string|null`): the table name (or list of tables), if you want to set it up-front.

```php
$db->update('users')
    ->set(['active' => 0])
    ->where(['last_login <' => '2025-01-01'])
    ->execute();
```

#### **Create a DELETE query** (`delete()`)

Creates a `DeleteQuery` for deleting rows.

Arguments:
- `$alias` (`array|string`): the alias (or aliases) to delete.

```php
$db->delete()
    ->from('sessions')
    ->where(['expires <' => time()])
    ->execute();
```

#### **Create a batch UPDATE query** (`updateBatch()`)

Creates an `UpdateBatchQuery`, typically used to update multiple rows with a single statement.

Arguments:
- `$table` (`string|null`): the table name.

```php
$db->updateBatch('users')
    ->set(
        [
            ['id' => 1, 'active' => 1],
            ['id' => 2, 'active' => 0],
        ],
        'id'
    )
    ->execute();
```

#### **Create an UPSERT query** (`upsert()`)

Creates an `UpsertQuery` for inserting rows and updating on conflict, when supported by the current driver.

Arguments:
- `$conflictKeys` (`array|string`): the conflict key (or keys).

```php
$db->upsert('id')
    ->into('users')
    ->values([['id' => 1, 'email' => 'a@example.com']])
    ->execute();
```

#### **Execute parameterized SQL** (`execute()`)

Executes SQL using a prepared statement with bound parameters and returns a `ResultSet`.

Arguments:
- `$sql` (`string`): the SQL string (positional `?` or named `:param` placeholders).
- `$params` (`array<int|string, mixed>`): the values to bind.

```php
$result = $db->execute(
    'SELECT id FROM users WHERE email = :email',
    ['email' => 'a@example.com']
);
```

#### **Read affected row count** (`affectedRows()`)

Returns the number of affected rows for the most recent executed statement.

```php
$db->update('users')
    ->set(['active' => 1])
    ->where(['id' => 1])
    ->execute();

$affected = $db->affectedRows();
```

#### **Run a query directly** (`query()`)

Executes a raw SQL query and returns a `ResultSet`.

```php
$result = $db->query('SELECT 1');
```

For deeper coverage of binding, literals, and safe raw fragments, see [Database queries](queries.md) (especially [Binding and expressions](queries.md#binding-and-expressions) and [Raw SQL fragments](queries.md#raw-sql-fragments)).

#### **Run work in a transaction** (`transactional()`)

Executes a callback inside a transaction. If the callback throws, the transaction is rolled back and the exception is rethrown. If the callback returns `false`, the transaction is rolled back and `false` is returned.

Arguments:
- `$callback` (`Closure(Connection): mixed`): the callback to run.

```php
$ok = $db->transactional(function($db) {
    $db->insert()
        ->into('audit_log')
        ->values(['event' => 'test'])
        ->execute();

    return false;
});
```

#### **Begin a transaction** (`begin()`)

Begins a transaction (or a savepoint when nested transactions are enabled).

```php
$db->begin();
```

#### **Commit a transaction** (`commit()`)

Commits the current transaction (or releases a savepoint when nested).

```php
$db->commit();
```

#### **Roll back a transaction** (`rollback()`)

Rolls back the current transaction (or rolls back to a savepoint when nested).

```php
$db->rollback();
```

#### **Get the transaction nesting level** (`getSavePointLevel()`)

Returns the current transaction nesting level (0 when not in a transaction).

```php
$level = $db->getSavePointLevel();
```

#### **Run callbacks after commit** (`afterCommit()`)

Queues a callback to run after the outermost transaction is committed. If no transaction is active, the callback runs immediately.

Arguments:
- `$callback` (`Closure`): the callback.
- `$priority` (`int`): the callback priority.
- `$key` (`string|null`): an optional key to replace an existing queued callback.

```php
$db->begin();

$db->afterCommit(fn() => null);

$db->commit();
```

#### **Get the last insert id** (`insertId()`)

Returns the last inserted id from the underlying PDO connection.

```php
$id = $db->insertId();
```

#### **Read the connection config** (`getConfig()`)

Returns the merged connection configuration for this instance (base defaults, handler defaults, and your provided options).

```php
$config = $db->getConfig();
```

#### **Check whether a transaction is active** (`inTransaction()`)

Returns whether a transaction is currently in progress.

```php
if ($db->inTransaction()) {
    $db->rollback();
}
```

#### **Read or set the connection charset** (`getCharset()`, `setCharset()`)

Reads the current connection charset, or sets it for the current connection.

Arguments:
- `$charset` (`string`): the charset to set.

```php
$current = $db->getCharset();
$db->setCharset($current);
```

#### **Read the server version** (`version()`)

Returns the database server version string.

```php
$version = $db->version();
```

#### **Disconnect from the database** (`disconnect()`)

Disconnects the underlying PDO connection and returns whether it was disconnected.

```php
$db->disconnect();
```

#### **Enable or disable query logging** (`enableQueryLogging()`, `disableQueryLogging()`)

Enables or disables query logging for this connection instance.

```php
$db->enableQueryLogging();
$db->disableQueryLogging();
```

#### **Enable or disable foreign key checks** (`enableForeignKeys()`, `disableForeignKeys()`)

Enables or disables foreign key checks for the current connection, using the driver-specific implementation.

```php
$db->disableForeignKeys();
$db->enableForeignKeys();
```

#### **Truncate a table** (`truncate()`)

Truncates a table using the driver-specific implementation.

Arguments:
- `$tableName` (`string`): the table to truncate.

```php
$db->truncate('audit_log');
```

### `MysqlConnection`

#### **Get the connection collation** (`getCollation()`)

Returns the current MySQL collation for the connection.

```php
use Fyre\DB\Handlers\Mysql\MysqlConnection;

$db = db();

if ($db instanceof MysqlConnection) {
    $collation = $db->getCollation();
}
```

### `PostgresConnection`

#### **Get the active schema** (`getSchema()`)

Returns the current schema search path for the connection.

```php
use Fyre\DB\Handlers\Postgres\PostgresConnection;

$db = db();

if ($db instanceof PostgresConnection) {
    $schema = $db->getSchema();
}
```

#### **Set the active schema** (`setSchema()`)

Sets the schema search path for the connection.

Arguments:
- `$schema` (`string`): the schema name.

```php
use Fyre\DB\Handlers\Postgres\PostgresConnection;

$db = db();

if ($db instanceof PostgresConnection) {
    $db->setSchema('public');
}
```

### `SqliteConnection`

#### **Check for SQLite sequences** (`hasSequences()`)

Returns whether the database contains the `sqlite_sequence` table.

```php
use Fyre\DB\Handlers\Sqlite\SqliteConnection;

$db = db();

if ($db instanceof SqliteConnection) {
    $hasSequences = $db->hasSequences();
}
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `ConnectionManager::use()` creates the connection instance the first time a key is requested and returns the same shared instance for subsequent calls.
- Connection handlers call `connect()` during construction, so failures surface when you call `use()` or `build()`.
- Building fails when a config is missing `className` or `className` does not extend `Fyre\DB\Connection`.
- `ConnectionManager::setConfig()` throws when the key already exists; use `unload()` or `clear()` before re-registering.
- `Connection::execute()` and `Connection::rawQuery()` dispatch the `Db.query` event with the executed SQL (and bound params for `execute()`); see [Events](../events/index.md).
- Nested transactions use savepoints when you call `begin()` inside an active transaction.
- For file-backed SQLite databases, the handler applies `mask` when creating a new database file.

## Related

- [Database](index.md)
- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Contextual attributes](../core/contextual-attributes.md)
- [Logging](../logging/index.md)
- [Events](../events/index.md)
- [Database queries](queries.md)
- [Schema](schema.md)
- [Forge](forge.md)
- [Database Migrations](migrations.md)
- [Database types](types.md)
