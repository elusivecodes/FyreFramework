# Database connections

Use database connections to configure one or more databases and resolve the one a given piece of code should use.

Most applications define a single `default` connection and only add more when they need separate databases, credentials, or drivers.

## Table of Contents

- [Start here](#start-here)
- [Connection configuration](#connection-configuration)
  - [Common connection options](#common-connection-options)
  - [Example configuration](#example-configuration)
- [Built-in connection handlers](#built-in-connection-handlers)
  - [MySQL](#mysql)
  - [PostgreSQL](#postgresql)
  - [SQLite](#sqlite)
- [Selecting a connection](#selecting-a-connection)
- [Building one-off connections](#building-one-off-connections)
- [Managing connections](#managing-connections)
- [Running queries](#running-queries)
- [Transactions](#transactions)
- [Database locks](#database-locks)
- [Connection utilities](#connection-utilities)
- [Troubleshooting](#troubleshooting)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In most applications:

- define one or more connections under the `Database` config key
- resolve the default connection with `db()` or `ConnectionManager::use()`
- pass a connection key when you need a non-default database
- use `build()` only for temporary connections you do not want to store or share

Most code can stay database-agnostic once the connection is configured.

```php
use Fyre\DB\ConnectionManager;

$connections = app(ConnectionManager::class);

$default = $connections->use();
$analytics = $connections->use('analytics');
```

## Connection configuration

Define connections under the `Database` key in your config (see [Config](../core/config.md)). Each named entry is an options array passed to the selected connection handler.

### Common connection options

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

You can resolve connections by key directly:

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

## Managing connections

`ConnectionManager` keeps configuration and loaded instances separate. The first call to `use($key)` builds a connection; later calls return that same instance.

| Method | Purpose |
| --- | --- |
| `getConfig($key = null)` | read one configuration, or all configurations |
| `hasConfig($key = 'default')` | check whether a configuration exists |
| `isLoaded($key = 'default')` | check whether a connection has been built |
| `setConfig($key, $options)` | add a runtime configuration |
| `unload($key = 'default')` | remove a configuration and its loaded connection |
| `clear()` | remove every configuration and loaded connection |

`setConfig()` throws when the key already exists. Unload the existing entry before replacing it.

## Running queries

Once you have a `Connection`, most day-to-day database work is done through query builder objects. Each builder compiles to SQL and executes through the connection (usually via `Query::execute()`).

You can also run SQL directly on the connection when needed:

- `execute($sql, $params)` for parameterized SQL with bound values
- `query($sql)` for direct SQL that should return a normal `ResultSet`

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
- Use `Connection::execute()` when you need direct SQL with bound parameters.
- Use `Connection::query()` when you need to run direct SQL and want the normal `ResultSet` wrapper.
- Use `Query::literal()` only for safe, deliberate SQL fragments (like functions or column expressions).
- Use `Connection::rawQuery()` only when you specifically need the underlying `PDOStatement`.
- Avoid embedding user input into literals or raw snippets; see [Raw SQL fragments](queries.md#raw-sql-fragments).

## Transactions

Use `transactional()` for work that should commit together. An exception rolls the transaction back and is rethrown; returning `false` rolls it back and returns `false`.

```php
$saved = $db->transactional(function($db) {
    $db->insert()
        ->into('audit_log')
        ->values([['event' => 'user.created']])
        ->execute();

    return true;
});
```

For manual control, use `begin()`, `commit()`, and `rollback()`. Nested transactions use savepoints, and `getSavePointLevel()` returns the current nesting level.

`afterCommit($callback, $priority = 1, $key = null)` schedules work after the outermost commit. When no transaction is active, the callback runs immediately. A key can be used to replace an already queued callback.

## Database locks

Use `Lock` when work must not run concurrently for the same name:

```php
$lock = db()->lock('daily-report', 60);

if ($lock->acquire(5)) {
    try {
        // Perform protected work and call refresh() before the lease expires.
    } finally {
        $lock->release();
    }
}
```

Locks with different names do not block each other. Before using database locks independently of migrations, initialize lock storage for the selected connection:

```bash
app db:lock:setup --db=default
```

The migration runner initializes this storage automatically before an actual migrate or rollback operation. `Lock::acquire()` does not perform DDL. Expired lock rows are not removed automatically, but they do not prevent their lock names from being acquired again. Only the current owner can refresh or release an active lock. Run or schedule `app db:lock:purge --db=default` to remove expired rows.

## Connection utilities

| Method | Purpose |
| --- | --- |
| `affectedRows()` | get the affected row count from the latest statement |
| `insertId()` | get the last inserted ID from PDO |
| `getConfig()` | inspect this connection's merged configuration |
| `inTransaction()` | check whether a transaction is active |
| `getCharset()` / `setCharset($charset)` | read or change the connection charset |
| `version()` | get the database server version |
| `disconnect()` | close the PDO connection |
| `enableQueryLogging()` / `disableQueryLogging()` | control logging for this instance |
| `enableForeignKeys()` / `disableForeignKeys()` | control foreign-key checks |
| `truncate($tableName)` | truncate a table using driver-specific SQL |

`MysqlConnection::getCollation()` returns the current MySQL collation. `PostgresConnection::getSchema()` reads the configured schema and `setSchema($schema)` changes its search path.

## Troubleshooting

Common issues when setting up connections:

- If you get an `InvalidArgumentException` when calling `use()` / `build()`, make sure your connection config includes a valid `className` that extends `Fyre\DB\Connection` (see [Connection configuration](#connection-configuration)).
- If you call `use()` with a key that has not been configured, connection creation will fail because there is no stored config for that key.
- If a connection fails immediately on `use()`, connection handlers call `connect()` during construction, so network/credential/database-name errors surface as soon as you resolve the connection. Double-check `host`, `port`, `username`, `password`, and `database`.
- If SQLite reports “unable to open database file”, verify the directory for your SQLite file exists and is writable by the PHP process. Use an absolute path if your working directory differs between environments.
- If query logging is enabled but nothing is written, `log: true` emits debug-level logs with the `queries` scope. Ensure your logger is configured for `debug` and includes the `queries` scope (see the example under [Example configuration](#example-configuration)).

## Behavior notes

- Connection handlers connect during construction, so credential, host, and database-name errors usually surface as soon as you call `use()` or `build()`.
- Building fails when a config is missing `className` or `className` does not extend `Fyre\DB\Connection`.
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
