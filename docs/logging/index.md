# Logging

Configure one or more PSR-3 handlers, then use `Fyre\Log\LogManager` to write to a specific handler or every handler that matches a level and scope.

## Table of Contents

- [Start here](#start-here)
- [Configuring handlers](#configuring-handlers)
  - [Common handler options](#common-handler-options)
  - [`FileLogger` options](#filelogger-options)
  - [`ConsoleLogger` options](#consolelogger-options)
  - [Example configuration](#example-configuration)
- [Built-in handlers](#built-in-handlers)
- [Writing log messages](#writing-log-messages)
  - [Fan-out with `handle()`](#fan-out-with-handle)
  - [A single handler with `use()`](#a-single-handler-with-use)
  - [Escape interpolation placeholders](#escape-interpolation-placeholders)
- [Managing handlers](#managing-handlers)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Configure a handler under the `Log` key in `config/app.php`:

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

$logs->handle('error', 'Payment failed for user {id}', ['id' => 123]);
```

## Configuring handlers

Handler configuration is read from the `Log` key in your config (see [Config](../core/config.md)). Each named handler config is an options array passed to the selected handler class.

### Common handler options

These options apply to all handlers that extend `Fyre\Log\Logger`:

| Option | Type | Default | Purpose |
| --- | --- | --- | --- |
| `className` | `class-string<Fyre\Log\Logger>` | required | handler class to build |
| `levels` | `string\|string[]\|null` | `null` | allowed levels; `null` allows every level |
| `scopes` | `string\|string[]\|null` | `[]` | allowed scopes; `[]` matches only unscoped messages and `null` matches every scope |
| `dateFormat` | `string` | `Y-m-d H:i:s` | timestamp format used by handlers that include dates |

Both `levels` and `scopes` accept a single string, a list of strings, or `null`:

```php
use Fyre\Log\Handlers\FileLogger;

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

Scope matching is exact:

| Configured `scopes` | Messages accepted |
| --- | --- |
| `[]` | unscoped messages only |
| `null` | all scoped and unscoped messages |
| `'payments'` | messages whose scope includes `payments` |
| `['payments', 'security']` | messages whose scope includes either value |

These filters are applied by `LogManager::handle()`. Calling PSR-3 methods directly on a handler returned by `use()` writes to that handler without running its level or scope filter.

### `FileLogger` options

`FileLogger` (`Fyre\Log\Handlers\FileLogger`) supports these additional options:

| Option | Type | Default | Purpose |
| --- | --- | --- | --- |
| `path` | `string` | `<system temp>/fyre/logs` | directory containing log files |
| `file` | `string\|null` | `null` | base file name; `null` uses the log level |
| `suffix` | `string\|null` | `null` | suffix after the base name; CLI defaults to `-cli` when omitted |
| `extension` | `string` | `log` | extension without a leading dot; an empty string omits it |
| `maxSize` | `int` | `1048576` | size in bytes at which the active file is rotated |
| `mask` | `int\|null` | `null` | permissions applied when a new file is created |

The directory is created when necessary. For durable production logs, configure an application-owned path explicitly.

### `ConsoleLogger` options

`ConsoleLogger` (`Fyre\Log\Handlers\ConsoleLogger`) adds one option:

| Option | Type | Default | Purpose |
| --- | --- | --- | --- |
| `stream` | `string` | `php://stderr` | writable stream URI |

### Example configuration

```php
use Fyre\Log\Handlers\ArrayLogger;
use Fyre\Log\Handlers\ConsoleLogger;
use Fyre\Log\Handlers\FileLogger;

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
        'console' => [
            'className' => ConsoleLogger::class,
            'stream' => 'php://stderr',
        ],
    ],
];
```

## Built-in handlers

Fyre includes these handlers under `Fyre\Log\Handlers\*`. Custom handlers extend `Fyre\Log\Logger` and are selected through `className`.

| Handler | Use |
| --- | --- |
| `FileLogger` | writes timestamped messages to files and rotates each active file at `maxSize` |
| `ConsoleLogger` | writes timestamped messages to a stream, normally standard error |
| `ArrayLogger` | keeps messages without timestamps in memory for inspection, primarily in tests |

By default, `FileLogger` uses one file per level: `error.log` for a web request and `error-cli.log` for a CLI process. Set `file` to combine levels in one file. For test assertions, prefer [Log Testing](../testing/logging.md).

## Writing log messages

Handlers are PSR-3 loggers, so you can call `$logger->info()`, `$logger->error()`, and the other standard level methods.

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
$logs->handle('info', 'User id: {id}; literal placeholder: \{id}', ['id' => 123]);
```

The escaped `\{id}` placeholder is written as `{id}` without the backslash.

Context values are serialized when possible. Values that cannot be converted are written as `[unhandled type Type]`.

When they are not supplied in the context array, the special placeholders `{get_vars}`, `{post_vars}`, `{server_vars}`, `{session_vars}`, and `{backtrace}` read directly from request or runtime state. They can expose secrets or personal data, so avoid them in production logs unless the output is known to be safe.

## Managing handlers

| Task | Method | Behavior |
| --- | --- | --- |
| Write to matching handlers | `handle($level, $message, $data = [], $scope = null)` | validates the level, then writes to every configured handler whose level and scope filters match |
| Resolve a shared handler | `use($key = 'default')` | builds the configured handler on first use and reuses it afterward |
| Build a one-off handler | `build($options)` | builds from `className` without storing the result |
| Add runtime config | `setConfig($key, $options)` | rejects a key that is already configured |
| Read config | `getConfig($key = null)` | returns one config or all configs |
| Check config | `hasConfig($key = 'default')` | reports whether the key is configured |
| Check loaded state | `isLoaded($key = 'default')` | reports whether the shared handler has been built |
| Unload a handler | `unload($key = 'default')` | removes both the shared instance and its config |
| Reset the manager | `clear()` | removes every shared instance and config |

## Behavior notes

- `LogManager::handle()` accepts `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, and `debug`. Level matching is case-sensitive, and any other value causes a `BadMethodCallException`.
- `FileLogger` throws when its path cannot be created or `maxSize` is not positive. Once constructed, file and stream write failures are suppressed rather than raised as logging exceptions.
- `ConsoleLogger` requires `stream` to be a string. An unavailable stream leaves the handler unable to write but does not make later `log()` calls throw.

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Contextual attributes](../core/contextual-attributes.md)
