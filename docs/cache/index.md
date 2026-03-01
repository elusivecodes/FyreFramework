# Cache

🧭 Cache covers configuring cache handlers and using them to reuse expensive values within a request (and, depending on the handler, across requests and processes). `Fyre\Cache\CacheManager` builds and shares configured cache handlers (PSR-16 Simple Cache).

## Table of Contents

- [Quick start](#quick-start)
- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Configuring caches](#configuring-caches)
  - [Base cache options](#base-cache-options)
  - [Example configuration](#example-configuration)
- [Built-in cache handlers](#built-in-cache-handlers)
  - [Array handler](#array-handler)
  - [File handler](#file-handler)
  - [Redis handler](#redis-handler)
  - [Memcached handler](#memcached-handler)
  - [Null handler](#null-handler)
- [Selecting a cache](#selecting-a-cache)
- [Common operations](#common-operations)
- [Method guide](#method-guide)
  - [`CacheManager`](#cachemanager)
  - [`Cacher`](#cacher)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Quick start

Pick a path based on what you’re doing:

- **Getting caching working in a typical app**: configure a `Cache.default` handler in [Config](../core/config.md), then resolve it with `CacheManager::use()` (or `cache()` if helpers are loaded).
- **Reducing repeated work**: start with `remember()` to cache expensive values with a TTL.
- **Swapping backends by environment**: keep call sites the same and change only `className` and handler options in config.
- **Debugging “why is nothing cached?”**: check whether caching is enabled (`CacheManager::isEnabled()`); caching starts disabled when `App.debug` is enabled.

## Purpose

🎯 Caching is a good fit when you need to:

- avoid recomputing expensive values within a request
- share computed results across requests (for filesystem and network-backed handlers)
- swap cache backends by environment without changing call sites

## Mental model

🧠 `Fyre\Cache\CacheManager` reads cache configurations from [Config](../core/config.md) (the `Cache` key) and provides `Fyre\Cache\Cacher` instances by key.

- `CacheManager::use()` returns one shared handler instance per key.
- `CacheManager::build()` creates a new handler instance from options without storing or sharing it.
- Handlers implement `Psr\SimpleCache\CacheInterface`; `Cacher` adds convenience methods like `remember()`, `increment()`, and `decrement()`.

## Configuring caches

Cache configuration is read from the `Cache` key in your config (see [Config](../core/config.md)). Each named cache config is an options array passed to the selected handler.

### Base cache options

These options apply to all handlers:

- `className` (`class-string<Fyre\Cache\Cacher>`): the cache handler class to build (for example `FileCacher::class`).
- `prefix` (`string`): a string applied to every cache key (default: `''`).
- `expire` (`int|null`): default TTL in seconds, used when a method call does not provide an explicit TTL (default: `null`).

Handler-specific options are documented below.

### Example configuration

```php
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Cache\Handlers\RedisCacher;

return [
    'Cache' => [
        'default' => [
            'className' => FileCacher::class,
            'path' => 'tmp/cache',
            'prefix' => 'app_',
            'expire' => 3600,
        ],
        'redis' => [
            'className' => RedisCacher::class,
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 1,
            'prefix' => 'app:',
        ],
    ],
];
```

## Built-in cache handlers

The options below are specific to the built-in handlers under `Fyre\Cache\Handlers\*`.

### Array handler

Caches values in an in-memory array for the current PHP process (`Fyre\Cache\Handlers\ArrayCacher`).

- No handler-specific options.

### File handler

Caches values on the filesystem (`Fyre\Cache\Handlers\FileCacher`).

Make sure the configured `path` exists and is writable by the PHP process.

Options:

- `path` (`string`): default `/tmp/cache` (in an application, you’ll usually set this to something like `tmp/cache`)
- `mode` (`int`): default `0640` (applied when creating a new cache file)

### Redis handler

Caches values using Redis (`Fyre\Cache\Handlers\RedisCacher`).

Requires `ext-redis` and a reachable Redis server.

Options:

- `host` (`string`): default `127.0.0.1`
- `password` (`string|null`): default `null`
- `port` (`int|string`): default `6379`
- `database` (`int|string|null`): default `null`
- `timeout` (`int|string`): default `0`
- `persist` (`bool`): default `true`
- `tls` (`bool`): default `false`
- `ssl` (`array`): keys `key`, `cert`, `ca` (all default `null`)

### Memcached handler

Caches values using Memcached (`Fyre\Cache\Handlers\MemcachedCacher`).

Requires `ext-memcached` and a reachable Memcached server.

Options:

- `host` (`string`): default `127.0.0.1`
- `port` (`int|string`): default `11211`
- `weight` (`int`): default `1`

### Null handler

No-op handler (`Fyre\Cache\Handlers\NullCacher`). Reads always return the provided default, and writes are ignored.

- No handler-specific options.

## Selecting a cache

Use a cache key to select which stored config to use. When no key is provided, `CacheManager::DEFAULT` (`default`) is used.

```php
use Fyre\Cache\CacheManager;

$caches = app(CacheManager::class);

$default = $caches->use();
$redis = $caches->use('redis');
```

If helpers are loaded, you can resolve a cache handler by key directly (see [Helpers](../core/helpers.md)):

```php
$default = cache();
$redis = cache('redis');
```

⚠️ Helpers are not available by default. They are defined in `config/functions.php` and must be loaded (see [Helpers](../core/helpers.md)).

## Common operations

Cache handlers implement `Psr\SimpleCache\CacheInterface` (`get()`, `set()`, `delete()`, `clear()`, and the `*Multiple()` variants). The `Cacher` base class also provides higher-level helpers.

```php
$cache = $caches->use();
// If helpers are loaded, you can also do: $cache = cache();

$value = $cache->get('report.123');
$cache->set('report.123', $value, 300);

$value = $cache->remember('report.123', static fn() => buildReport(), 300);

$cache->increment('counters.reports_generated');
```

## Method guide

This section focuses on the methods you’ll use most when selecting handlers and working with cached values.

### `CacheManager`

#### **Get a shared cache handler** (`use()`)

Returns the shared cache handler instance for a config key. If the handler has not been created yet, it is built from the stored config and cached.

Arguments:
- `$key` (`string`): the cache config key (defaults to `default`).

```php
$default = $caches->use();
$redis = $caches->use('redis');
```

#### **Build a cache handler instance** (`build()`)

Builds a new handler instance from an options array (without storing or sharing it).

Arguments:
- `$options` (`array<string, mixed>`): cache options including `className`.

```php
use Fyre\Cache\Handlers\ArrayCacher;

$cache = $caches->build([
    'className' => ArrayCacher::class,
    'prefix' => 'tmp_',
]);
```

#### **Check whether caching is enabled** (`isEnabled()`)

Returns whether caching is currently enabled for this `CacheManager` instance.

```php
$enabled = $caches->isEnabled();
```

#### **Enable caching** (`enable()`)

Enables caching.

```php
$caches->enable();
```

#### **Disable caching** (`disable()`)

Disables caching.

```php
$caches->disable();
```

#### **Read stored configuration** (`getConfig()`)

Returns the stored cache config array. When called with no key, it returns all stored configs.

Arguments:
- `$key` (`string|null`): the cache config key, or `null` to return all configs.

```php
$all = $caches->getConfig();
$default = $caches->getConfig('default');
```

#### **Add configuration at runtime** (`setConfig()`)

Stores a cache configuration under a key.

Arguments:
- `$key` (`string`): the cache config key.
- `$options` (`array<string, mixed>`): cache options for the handler.

```php
use Fyre\Cache\Handlers\FileCacher;

$caches->setConfig('local', [
    'className' => FileCacher::class,
    'path' => 'tmp/cache',
]);
```

#### **Unload a cache** (`unload()`)

Unloads a cache key by removing both the cached handler instance and the stored configuration.

Arguments:
- `$key` (`string`): the cache config key (defaults to `default`).

```php
$caches->unload('redis');
```

### `Cacher`

#### **Get or compute a value** (`remember()`)

Retrieves a value from the cache, or computes and stores a new value when the key is missing.

Arguments:
- `$key` (`string`): the cache key.
- `$callback` (`Closure`): callback that generates the value on a miss.
- `$expire` (`DateInterval|int|null`): time to live for this value, in seconds or as a `DateInterval` (defaults to the handler configuration).

```php
$value = $cache->remember('reports.latest', static fn() => buildLatestReport(), 600);
```

#### **Increment a numeric value** (`increment()`)

Increments a cached numeric value.

Arguments:
- `$key` (`string`): the cache key.
- `$amount` (`int`): amount to increment (default: `1`).

```php
$cache->increment('counters.reports_generated');
```

#### **Decrement a numeric value** (`decrement()`)

Decrements a cached numeric value.

Arguments:
- `$key` (`string`): the cache key.
- `$amount` (`int`): amount to decrement (default: `1`).

```php
$cache->decrement('counters.reports_generated');
```

#### **Read handler configuration** (`getConfig()`)

Returns the handler configuration array after defaults are applied.

```php
$config = $cache->getConfig();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- When caching is disabled, `CacheManager::use()` (and the `cache()` helper) always returns a `NullCacher` regardless of configuration. By default, caching starts disabled when `App.debug` is enabled (see [Config](../core/config.md)).
- When caching is enabled, building a handler without a valid `className` (missing, not a string, or not a `Cacher` subclass) throws `Fyre\Cache\Exceptions\InvalidArgumentException`.
- Cache keys are rejected if they contain any of these characters: `{ } ( ) / \ @ :` (the key is validated before the configured `prefix` is applied).
- `FileCacher` rejects a `prefix` that contains the system directory separator, and `FileCacher::clear()` only removes cache files that match the configured prefix.
- `RedisCacher::clear()` flushes the entire Redis database when no prefix is configured. When a prefix is configured, it scans and deletes matching keys.
- `RedisCacher::set()` supports scalar types, arrays, objects, and `null`. Other value types cause `set()` to return `false` without writing.

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
