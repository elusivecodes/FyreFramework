# Config

`Fyre\Core\Config` stores application settings as a nested array. It supports dot-notation keys and can merge PHP config files from multiple directories.

## Table of Contents

- [Start here](#start-here)
- [Configuration model](#configuration-model)
- [Loading and overriding](#loading-and-overriding)
- [Managing config](#managing-config)
- [Settings used by framework services](#settings-used-by-framework-services)
- [Application config](#application-config)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In a typical application, `Engine` adds the directory identified by the `CONFIG` constant as a search path. Load a PHP config file by its base name, then read values with `config()`:

```php
config()->load('app');

$debug = config('App.debug', false);
```

Calling `config()` without arguments returns the shared `Config` instance. `config('A.B.C', $default)` reads a value from that instance; see [Helpers](helpers.md).

You can also inject `Config`:

```php
use Fyre\Core\Config;

function handler(Config $config): bool
{
    return (bool) $config->get('App.debug', false);
}
```

When composing the runtime manually, add the application paths before loading files:

```php
$config = config();
$config->addPath('/path/to/config');
$config->addPath('/path/to/config/local');
$config->load('app');
```

## Configuration model

Keys passed to `get()`, `has()`, `set()`, `delete()`, and `consume()` are split on `.` and used to walk the stored array.

```php
$config->set('App.debug', false);

$debug = $config->get('App.debug');
```

`has()` checks keys rather than truthiness, so a key whose value is `null` is still present. `consume()` reads a value and then removes it.

## Loading and overriding

Each config file must be a PHP file that returns an array. `load('app')` looks for `app.php` in every configured path and merges each array into the current config.

Paths are processed in the order returned by `getPaths()`. Later paths override earlier paths with `array_replace_recursive()`. Use that ordering for local or environment-specific overrides:

```php
$config->addPath('/path/to/config');
$config->addPath('/path/to/config/local');
$config->load('app');
```

If both paths contain `app.php`, values from `/path/to/config/local/app.php` take precedence. Passing `prepend: true` to `addPath()` places a path at the start of the search order, giving it lower precedence than paths added after it.

This is recursive replacement rather than list concatenation; numeric array entries are replaced by index. Paths are normalized with `Fyre\Utility\Path::resolve()`, and equivalent paths are not added twice. Missing files and files that do not return arrays are ignored.

## Managing config

| Task | Method | Behavior |
| --- | --- | --- |
| Read a value | `get($key, $default = null)` | returns the default when the dot-notation key is missing |
| Check a key | `has($key)` | treats keys set to `null` as present |
| Set a value | `set($key, $value, $overwrite = true)` | writes a dot-notation key; preserves an existing target key when `$overwrite` is `false` |
| Read once | `consume($key, $default = null)` | returns the value, then deletes the key |
| Delete a value | `delete($key)` | removes a dot-notation key |
| Add a search path | `addPath($path, $prepend = false)` | normalizes and adds a unique path |
| Remove a search path | `removePath($path)` | normalizes the path before matching it |
| Inspect search paths | `getPaths()` | returns paths in load order |
| Load a file | `load($file)` | loads and merges `<file>.php` from every search path |
| Reset config | `clear()` | removes all values and search paths |

`set()` accepts `*` as an intermediate segment when the same remaining path should be applied to every child:

```php
$config->set('Database.*.log', true);
```

## Settings used by framework services

The following map covers the main framework-owned namespaces. Application code can store its own settings alongside them.

| Config key | Used by |
| --- | --- |
| `App.baseUri` | [Routing](../routing/index.md) |
| `App.charset` | [Mail](../mail/index.md) and HTML output helpers |
| `App.debug` | [Cache](../cache/index.md) and application error handling |
| `App.defaultLocale` | [Lang](lang.md), [HTTP](../http/index.md), and [Formatter](../utilities/formatter.md) |
| `App.supportedLocales`, `App.trustProxy`, `App.trustedProxies` | [HTTP](../http/index.md) and [Rate Limiting](../security/rate-limiting.md) |
| `App.defaultCurrency` | [Formatter](../utilities/formatter.md) |
| `Auth` | [Authentication](../auth/index.md) |
| `Cache` | [Cache](../cache/index.md) |
| `Csp`, `Csrf` | [Security](../security/index.md) |
| `Database` | [Database](../database/index.md) and [ORM](../orm/index.md) |
| `Encryption` | [Encryption](../security/encryption.md) |
| `Error` | [Error-handling middleware](../http/middleware.md#built-in-middleware) |
| `Log` | [Logging](../logging/index.md) |
| `Mail` | [Mail](../mail/index.md) |
| `Queue` | [Queue](../queue/index.md) |
| `Session` | [Sessions](../http/sessions.md) |

## Application config

Applications commonly keep their main settings in `config/app.php`:

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

Add subsystem configuration under the keys in the table above. Each linked guide documents its available classes, options, and defaults.

## Behavior notes

- `get()` returns the default when a segment is missing or an intermediate segment is not an array.
- `consume()` always attempts to delete the key after reading it, including when the default is returned.
- Adding or removing paths does not reload files that were loaded previously; call `load()` again when you want to merge them.
- Because missing and non-array config files are ignored, validate required application settings during bootstrapping when their absence should be fatal.

## Related

- [Helpers](helpers.md)
- [Container](container.md)
- [Engine](engine.md)
- [Lang](lang.md)
