# Logging

Use the logging subsystem when you want to configure handlers and write PSR-3 log messages.

Most applications configure one or more handlers, then write messages through `LogManager`.

## Table of Contents

- [Start here](#start-here)
- [Configuring handlers](#configuring-handlers)
  - [Common handler options](#common-handler-options)
  - [Scope filtering example](#scope-filtering-example)
  - [`FileLogger` options](#filelogger-options)
  - [Example configuration](#example-configuration)
- [Built-in handlers](#built-in-handlers)
  - [File handler](#file-handler)
  - [Array handler](#array-handler)
- [Writing log messages](#writing-log-messages)
  - [Fan-out with `handle()`](#fan-out-with-handle)
  - [A single handler with `use()`](#a-single-handler-with-use)
  - [Escape interpolation placeholders](#escape-interpolation-placeholders)
- [Method guide](#method-guide)
  - [`LogManager`](#logmanager)
  - [`Logger`](#logger)
  - [`FileLogger`](#filelogger)
  - [`ArrayLogger`](#arraylogger)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Most applications do two things:

1. configure one or more handlers under the `Log` config key, and
2. call `LogManager::handle()` (fan-out) or `LogManager::use()` (a single handler).

Minimal config (for example `config/app.php`):

```php
use Fyre\Log\Handlers\FileLogger;

return [
    'Log' => [
        'default' => [
            'className' => FileLogger::class,
            'path' => 'tmp/logs',
        ],
    ],
];
```

```php
use Fyre\Log\LogManager;

$logs = app(LogManager::class);

$logs->handle('error', 'Payment failed for user {id}', ['id' => 123], 'payments');
```

## Configuring handlers

Handler configuration is read from the `Log` key in your config (see [Config](../core/config.md)). Each named handler config is an options array passed to the selected handler class.

Config examples assume any referenced handler classes (for example `FileLogger` / `ArrayLogger`) are already imported at the top of the config file.

### Common handler options

These options apply to all handlers that extend `Fyre\Log\Logger`:

- `className` (`class-string<Fyre\Log\Logger>`): the handler class to build.
- `levels` (`string|string[]|null`): allowed levels, or `null` to allow all levels (default: `null`).
- `scopes` (`string|string[]|null`): allowed scopes, `[]` to match only unscoped messages, or `null` to allow all scopes (default: `[]`).
- `dateFormat` (`string`): date format used when the handler includes timestamps in formatted output (default: `Y-m-d H:i:s`).

Both `levels` and `scopes` accept a single string, a list of strings, or `null`:

```php
return [
    'Log' => [
        'errors' => [
            'className' => FileLogger::class,
            'levels' => 'error',
            'scopes' => ['payments', 'security'],
        ],
        'all' => [
            'className' => FileLogger::class,
            'levels' => null,
            'scopes' => null,
        ],
    ],
];
```

### Scope filtering example

The default `scopes` value is `[]`, which means the handler matches only unscoped messages (`scope: null`).

```php
return [
    'Log' => [
        // scopes: [] (default) → matches only unscoped messages
        'default' => [
            'className' => FileLogger::class,
        ],

        // matches only messages logged with scope: 'payments'
        'payments' => [
            'className' => FileLogger::class,
            'scopes' => ['payments'],
        ],

        // scopes: null → matches any scope (including unscoped)
        'all' => [
            'className' => FileLogger::class,
            'scopes' => null,
        ],
    ],
];
```

### `FileLogger` options

`FileLogger` (the `Fyre\Log\Handlers\FileLogger` handler) supports these additional options:

- `path` (`string`): the folder to write log files into (default: `/var/log/`). If it does not exist, the handler attempts to create it. In most applications, you’ll want something like `tmp/logs` (system folders may require elevated permissions).
- `file` (`string|null`): the base filename (without extension) to write to. When `null`, the log level is used (one file per level).
- `suffix` (`string|null`): a suffix appended to the base filename. When running under CLI, `-cli` is used by default when `suffix` is not specified.
- `extension` (`string`): file extension without the leading dot (default: `log`). Set to an empty string to omit the extension.
- `maxSize` (`int`): the file size threshold (in bytes) that triggers rotation (default: `1048576`).
- `mask` (`int|null`): permissions applied when creating a new log file (only when the target file does not already exist).

### Example configuration

```php
return [
    'Log' => [
        'default' => [
            'className' => FileLogger::class,
            'path' => 'tmp/logs',
        ],
        'buffer' => [
            'className' => ArrayLogger::class,
            'levels' => ['debug', 'info'],
            'scopes' => null,
        ],
    ],
];
```

## Built-in handlers

Fyre includes a small set of handlers under `Fyre\Log\Handlers\*`. You can define custom handlers by extending `Fyre\Log\Logger` and referencing the class in `className`.

### File handler

`FileLogger` writes formatted messages to files under `path`. By default, it writes one file per log level (for example, `error.log`), but you can set `file` to write all levels into a single file.

In most applications, prefer a writable application folder like `tmp/logs` over system folders like `/var/log/`.

### Array handler

`ArrayLogger` stores formatted messages in memory for later inspection. This is primarily useful for tests and assertions.

## Writing log messages
Handlers are PSR-3 loggers, so you can call `$logger->info()`, `$logger->error()`, etc.

### Fan-out with `handle()`

Use `LogManager::handle()` to log a message to all configured handlers that match the level and scope:

```php
$logs->handle('error', 'Payment failed for user {id}', ['id' => 123], 'payments');
```

`log_message($type, $message, $data)` forwards to `LogManager::handle()`; see [Helpers](../core/helpers.md).

```php
log_message('error', 'Payment failed for user {id}', ['id' => 123]);
```

### A single handler with `use()`

To write to a specific configured handler, resolve it by key with `use()` and call PSR-3 methods on the returned `Logger` instance:

```php
$logger = $logs->use('default');
$logger->info('Background job {job} started', ['job' => 'sync']);
```

If you use contextual attributes, `Fyre\Core\Attributes\Log` can resolve a handler by key when the container is building an object or calling a callable (see [Contextual attributes](../core/contextual-attributes.md)).

### Escape interpolation placeholders

To log a literal placeholder (rather than interpolating it), escape it with a backslash:

```php
$logs->handle('info', 'User id: {id}', ['id' => 123]);
$logs->handle('info', 'Literal placeholder: \{id}');
```

## Method guide

This section focuses on the methods you’re most likely to use directly when configuring handlers and writing messages.

Examples below assume `$logs` is a `LogManager` instance and `$logger` is a `Logger` instance.

### `LogManager`

#### **Dispatch a message to handlers** (`handle()`)

Validate the log level and forward the message to all configured handlers that can handle the level and scope.

Arguments:
- `$level` (`string`): the log level (one of `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`).
- `$message` (`string`): the message to log.
- `$data` (`array<string, mixed>`): context values for message interpolation (defaults to `[]`).
- `$scope` (`array|string|null`): the scope(s) for handler filtering (defaults to `null`).

```php
$logs->handle('warning', 'Rate limit exceeded for {ip}', ['ip' => '127.0.0.1'], 'security');
```

#### **Get a handler** (`use()`)

Returns the handler for a config key. The first call creates it from config, and later calls return the same handler.

Arguments:
- `$key` (`string`): the handler config key (defaults to `default`).

```php
$logs->use()->error('Something went wrong');
```

#### **Build a handler instance** (`build()`)

Build a one-off handler from an options array without storing it on the manager.

Arguments:
- `$options` (`array<string, mixed>`): handler options including `className`.

```php
$logger = $logs->build([
    'className' => ArrayLogger::class,
    'levels' => ['debug', 'info'],
]);
```

#### **Register a handler config** (`setConfig()`)

Register a handler config at runtime.

Arguments:
- `$key` (`string`): the handler key to register.
- `$options` (`array<string, mixed>`): handler options including `className`.

```php
$logs->setConfig('buffer', [
    'className' => ArrayLogger::class,
    'levels' => ['debug', 'info'],
]);
```

#### **Read stored configuration** (`getConfig()`)

Returns the stored config array. When called with no key, it returns all stored configs.

Arguments:
- `$key` (`string|null`): the handler key, or `null` to return all configs.

```php
$all = $logs->getConfig();
$default = $logs->getConfig('default');
```

### `Logger`

#### **Check whether a handler can handle a message** (`canHandle()`)

Returns whether a handler matches the configured level and scope filters.

Arguments:
- `$level` (`string`): the log level to check.
- `$scope` (`array|string|null`): the scope(s) to check (defaults to `null`).

```php
if ($logs->use()->canHandle('debug', 'queries')) {
    $logs->use()->debug('Query logging is enabled');
}
```

### `ArrayLogger`

#### **Read buffered messages** (`read()`)

Return the buffered log content.

`ArrayLogger` stores formatted messages in memory without the date prefix that `FileLogger` includes by default.

```php
$logger = new ArrayLogger();
$logger->info('Queued job {id}', ['id' => 42]);

$messages = $logger->read();
```

#### **Clear buffered messages** (`clear()`)

Clear the buffered log content.

```php
$logger->clear();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `LogManager::handle()` validates levels against the supported list and is case-sensitive, so `error` is valid but `ERROR` is not.
- `LogManager::setConfig()` rejects duplicate keys.
- `LogManager::use()` does not ignore unknown keys. If the key has no stored config, handler building fails because there is no valid `className`.
- Scope filtering is opt-in: when a handler has the default `scopes` value of `[]`, it matches only when `scope` is `null`. Passing a scope will skip those handlers unless `scopes` is configured (or `scopes` is `null`).
- `FileLogger` creates the `path` folder when it does not exist (and throws if it cannot create it).
- `FileLogger` rotates by copying the current file when it reaches `maxSize` and then truncating the original file in place. If the destination file cannot be opened for appending, the write is skipped.
- `ArrayLogger` keeps messages in memory only and stores them without timestamps.
- Message interpolation supports `{key}` placeholders from your context array and special keys like `{get_vars}`, `{post_vars}`, `{server_vars}`, `{session_vars}`, and `{backtrace}`. Escape placeholders with a backslash (for example `\{id}`).
- When values are encoded as JSON during interpolation, encoding errors can throw and are not swallowed.
- Be careful with the special interpolation keys: `{get_vars}`, `{post_vars}`, `{server_vars}`, `{session_vars}`, and `{backtrace}` can include secrets or personal data. Avoid logging them in production unless you are sure the output is safe.

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Contextual attributes](../core/contextual-attributes.md)
