# Config

`Fyre\Core\Config` provides configuration storage and lookup using nested arrays and dot-notation keys. It’s a small, predictable API that other subsystems read from to configure themselves at runtime.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Configuration model](#configuration-model)
- [Loading and overriding](#loading-and-overriding)
  - [Example: override precedence](#example-override-precedence)
- [Services that read config](#services-that-read-config)
  - [App-level keys](#app-level-keys)
  - [Subsystem namespaces](#subsystem-namespaces)
- [Example `config/app.php`](#example-configappphp)
  - [Minimal example](#minimal-example)
  - [Extended example](#extended-example)
- [Method guide](#method-guide)
  - [Reading and writing values](#reading-and-writing-values)
  - [Loading config files](#loading-config-files)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Config centralizes application and subsystem settings so constructors can stay explicit while still being configurable.

It stores configuration in memory and can merge PHP config files from registered paths when you call `load()`.

`config()` can be used in two ways:

- `config()` returns the shared `Config` instance.
- `config('A.B.C', $default)` is shorthand for reading a config value directly (see [Helpers](helpers.md)).

If you prefer dependency injection (or if helpers aren’t loaded), inject `Config` where you need it:

```php
use Fyre\Core\Config;

function handler(Config $config): bool
{
    return (bool) $config->get('App.debug', false);
}
```

## Quick start

In a typical application, `Config` is resolved from the container and already has the default app and framework config paths registered by `Engine`.

```php
config()->load('app');

$debug = config()->get('App.debug', false);
```

If you are composing the runtime manually, or want to load additional config locations, register those paths yourself:

```php
$config = config();
$config->addPath('config');
$config->addPath('config/local');
$config->load('app');
```

## Configuration model

Config stores a single nested array. Keys passed to `get()`, `has()`, `set()`, `delete()`, and `consume()` are split on `.` and used to walk that nested structure.

- `get('A.B.C')` retrieves `$config['A']['B']['C']` when present, otherwise returns the provided default.
- `has('A.B.C')` checks for existence using `array_key_exists` at each level, so a key set to `null` still “exists”.
- `consume('A.B.C')` returns the same value as `get()` and then removes that key from the stored config.

## Loading and overriding

Config can load PHP config files (arrays) from one or more configured paths. Files are searched by base name (without extension) and loaded with `require`. Each `load()` call merges matching arrays into the existing in-memory config.

- `addPath()` stores a normalized version of the path (via `Fyre\Utility\Path::resolve()`), so equivalent paths aren’t duplicated.
- `load($file)` looks for `$file.'.php'` in each configured path, in the order stored by `getPaths()`.
- When multiple files are found, arrays are merged with `array_replace_recursive()` so later paths override earlier paths.
- `addPath($path, prepend: true)` inserts the path earlier in that search order, so later appended paths still override it when the same keys exist.
- Missing files and non-array results are ignored.

```php
$config->addPath('config');
$config->addPath('config/local');
$config->load('app');
```

### Example: override precedence

When the same config file exists in multiple paths, later paths override earlier paths.

For example, if `config/app.php` returns:

```php
return ['App' => ['debug' => false]];
```

…and `config/local/app.php` returns:

```php
return ['App' => ['debug' => true]];
```

Then after:

```php
$config->addPath('config');
$config->addPath('config/local');
$config->load('app');
```

`App.debug` resolves to `true`.

## Services that read config

This is a quick map of which services consume which config namespaces (not exhaustive).

### App-level keys

- [Routing](../routing/index.md) — `App.baseUri` (**critical** for routing when hosting under a path).
- [HTTP](../http/index.md) — `App.defaultLocale`, `App.supportedLocales` (read by `Fyre\Http\ServerRequest`).
- [Language (Lang)](lang.md) — `App.defaultLocale`.
- [Formatter](../utilities/formatter.md) — `App.defaultLocale`, `App.defaultCurrency`.
- [Cache](../cache/index.md) — `App.debug` (caching is disabled by default when `App.debug` is enabled).

### Subsystem namespaces

- [Auth](../auth/index.md) — `Auth.loginRoute`, `Auth.authenticators`, `Auth.identifier`.
- [Sessions](../http/sessions.md) — `Session` (**critical** if you use sessions).
- [Database](../database/index.md) — `Database` (**critical** if you use DB/ORM).
- [Cache](../cache/index.md) — `Cache`.
- [Logging](../logging/index.md) — `Log`.
- [Mail](../mail/index.md) — `Mail`.
- [Queue](../queue/index.md) — `Queue`.
- [Security](../security/index.md) — `Csrf`, `Csp`.

## Example `config/app.php`

In an application, `config/app.php` is typically the main place you define framework configuration because the default `Engine` setup registers the app config directory. `Config` itself only loads files from paths you add.

### Minimal example

```php
return [
    'App' => [
        'name' => 'MyApp',
        'debug' => false,
        'baseUri' => 'http://localhost:8000',
        'defaultLocale' => 'en_US',
        'supportedLocales' => ['en_US'],
    ],
];
```

### Extended example

```php
use Fyre\Auth\Authenticators\SessionAuthenticator;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\Http\Session\Handlers\FileSessionHandler;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Mail\Handlers\SmtpMailer;
use Fyre\Queue\Handlers\RedisQueue;

return [
    'App' => [
        'name' => 'MyApp',
        'debug' => false,
        'baseUri' => 'http://localhost:8000',
        'defaultLocale' => 'en_US',
        'supportedLocales' => ['en_US'],
        'defaultCurrency' => 'USD',
    ],

    'Auth' => [
        'loginRoute' => 'login',
        'authenticators' => [
            [
                'className' => SessionAuthenticator::class,
            ],
        ],
    ],

    'Cache' => [
        'default' => [
            'className' => FileCacher::class,
            'path' => 'tmp/cache',
        ],
    ],

    'Csp' => [
        'default' => [
            'default-src' => ['self'],
            'img-src' => ['self', 'data:'],
        ],
        'reportTo' => [
            'group' => 'csp',
            'max_age' => 10886400,
            'endpoints' => [
                ['url' => 'https://example.com/csp-report'],
            ],
        ],
    ],

    'Csrf' => [
        'field' => 'csrf_token',
        'header' => 'Csrf-Token',
        'cookie' => [
            'name' => 'CsrfToken',
            'secure' => true,
            'sameSite' => 'Lax',
        ],
    ],

    'Database' => [
        'default' => [
            'className' => SqliteConnection::class,
            'database' => 'tmp/app.sqlite',
        ],
    ],

    'Log' => [
        'default' => [
            'className' => FileLogger::class,
            'path' => 'tmp/logs',
        ],
    ],

    'Mail' => [
        'default' => [
            'className' => SmtpMailer::class,
            'host' => '127.0.0.1',
            'port' => 587,
            'tls' => true,
        ],
    ],

    'Queue' => [
        'default' => [
            'className' => RedisQueue::class,
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
    ],

    'Session' => [
        'path' => 'tmp/sessions',
        'cookie' => [
            'name' => 'FyreSession',
            'secure' => true,
            'sameSite' => 'Lax',
        ],
        'handler' => [
            'className' => FileSessionHandler::class,
        ],
    ],
];
```

## Method guide

This section focuses on the handful of `Config` methods you’ll use day-to-day: reading values, updating settings in code, and loading config files.

Unless noted otherwise, examples below assume you already have:

```php
$config = config();
```

### Reading and writing values

#### **Read a value** (`get()`)

Reads a value using dot notation, returning `$default` when the key is missing.

In application code, you’ll often use the `config()` helper for this; see [Helpers](helpers.md).

Arguments:
- `$key` (`string`): a dot-notation key like `App.debug`.
- `$default` (`mixed`): value to return when missing.

```php
$debug = $config->get('App.debug', false);
```

#### **Check whether a key exists** (`has()`)

Returns `true` when the key exists (even if the stored value is `null`).

Arguments:
- `$key` (`string`): a dot-notation key like `App.debug`.

```php
$hasDebug = $config->has('App.debug');
```

#### **Set a value** (`set()`)

Sets a value using dot notation.

Arguments:
- `$key` (`string`): a dot-notation key.
- `$value` (`mixed`): the value to set.
- `$overwrite` (`bool`): when `false`, existing values are preserved.

```php
use Fyre\DB\Handlers\Sqlite\SqliteConnection;

$config->set('Database.default.className', SqliteConnection::class);
$config->set('Database.default.database', 'tmp/app.sqlite');
```

`set()` also supports wildcard segments (`*`) to apply a remaining path to every child at a given level:

```php
$config->set('Database.*.log', true);
```

#### **Delete a value** (`delete()`)

Deletes a key using dot notation.

Arguments:
- `$key` (`string`): a dot-notation key.

```php
$config->delete('App.debug');
```

#### **Read and delete a value** (`consume()`)

Reads a value (like `get()`), then deletes it.

Arguments:
- `$key` (`string`): a dot-notation key.
- `$default` (`mixed`): value to return when missing.

```php
$value = $config->consume('App.once');
```

### Loading config files

#### **Add a config path** (`addPath()`)

Adds a path to search when calling `load()`.

Paths are normalized before storage, so equivalent paths are not duplicated. When multiple paths contain the same file, later paths override earlier paths.

Arguments:
- `$path` (`string`): the folder to search for config files.
- `$prepend` (`bool`): when `true`, inserts the path at the start (lower precedence than later paths).

```php
$config->addPath('config');
$config->addPath('config/local');
$config->load('app');
```

#### **Remove a config path** (`removePath()`)

Removes a path that was previously added.

Arguments:
- `$path` (`string`): the folder to remove.

```php
$config->removePath('config/local');
```

#### **Get configured paths** (`getPaths()`)

Returns the current list of search paths, in the order they’ll be processed.

```php
$paths = $config->getPaths();
```

#### **Load a config file** (`load()`)

Loads a PHP file that returns an array (by base name), merging it into the current config.

Arguments:
- `$file` (`string`): the base file name (without `.php`).

```php
$config->addPath('config');
$config->load('app');
```

#### **Clear all config data** (`clear()`)

Clears all loaded config data and configured paths.

```php
$config->clear();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `get()` returns the default when a segment is missing *or* when an intermediate segment exists but is not an array.
- `has()` treats `null` values as present (it checks keys, not truthiness).
- `consume()` removes the key after reading it, so use it only for “read once” values.
- `load()` ignores missing files and files that don’t return an array, which can hide typos if you don’t validate separately.

## Related

- [Helpers](helpers.md)
- [Container](container.md)
- [Engine](engine.md)
- [Cache](../cache/index.md)
- [Logging](../logging/index.md)
- [Mail](../mail/index.md)
- [Queue](../queue/index.md)
- [Sessions](../http/sessions.md)
- [Language (Lang)](lang.md)
- [Auth](../auth/index.md)
- [Database](../database/index.md)
- [Routing](../routing/index.md)
- [Security](../security/index.md)
