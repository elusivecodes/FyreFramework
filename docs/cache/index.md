# Cache

Use cache to store expensive values behind named cache handlers.

## Table of Contents

- [Start here](#start-here)
- [Configuring caches](#configuring-caches)
  - [Base cache options](#base-cache-options)
  - [Example configuration](#example-configuration)
- [Built-in cache handlers](#built-in-cache-handlers)
  - [Array handler](#array-handler)
  - [File handler](#file-handler)
  - [Redis handler](#redis-handler)
  - [Memcached handler](#memcached-handler)
  - [Null handler](#null-handler)
- [Using a cache](#using-a-cache)
- [Common operations](#common-operations)
  - [Tagged cache entries](#tagged-cache-entries)
  - [Cache locks](#cache-locks)
- [Method guide](#method-guide)
  - [`CacheManager`](#cachemanager)
  - [`Cacher`](#cacher)
  - [`Lock`](#lock)
  - [`TaggedCacher`](#taggedcacher)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Most applications follow the same flow:

- define one or more named caches in config
- resolve a cache with `CacheManager::use()` or the `cache()` helper
- use `remember()` for values you want to compute on a miss
- use tags when you want to invalidate groups of cached values
- use locks when shared work must not run concurrently

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
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Cache\Handlers\Redis\RedisCacher;

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

Caches values in an in-memory array for the current PHP process (`Fyre\Cache\Handlers\Array\ArrayCacher`).

- No handler-specific options.

### File handler

Caches values on the filesystem (`Fyre\Cache\Handlers\File\FileCacher`).

Make sure the configured `path` exists or can be created, and is writable by the PHP process.

Options:

- `path` (`string`): default `/tmp/cache` (in an application, you’ll usually set this to something like `tmp/cache`)
- `mode` (`int`): default `0640` (applied when creating a new cache file)

### Redis handler

Caches values using Redis (`Fyre\Cache\Handlers\Redis\RedisCacher`).

Requires `ext-redis` and a reachable Redis server.

Options:

- `host` (`string`): default `127.0.0.1`
- `password` (`string|null`): default `null`
- `port` (`int|string`): default `6379`
- `database` (`int|string|null`): default `null`
- `timeout` (`int|string`): default `0`
- `persist` (`bool`): default `true`
- `flushDatabase` (`bool`): default `false` (allows `clear()` to flush the selected Redis database when no `prefix` is configured)
- `tls` (`bool`): default `false`
- `ssl` (`array`): keys `key`, `cert`, `ca` (all default `null`)

### Memcached handler

Caches values using Memcached (`Fyre\Cache\Handlers\Memcached\MemcachedCacher`).

Requires `ext-memcached` and a reachable Memcached server.

Options:

- `host` (`string`): default `127.0.0.1`
- `port` (`int|string`): default `11211`
- `weight` (`int`): default `1`

### Null handler

No-op handler (`Fyre\Cache\Handlers\Null\NullCacher`). Reads always return the provided default, writes are ignored, `increment()` returns `$amount`, and `decrement()` returns `-$amount` without persisting a counter.

- No handler-specific options.

## Using a cache

Use a config key to choose which named cache to work with. When no key is provided, `default` is used.

When caching is disabled, newly resolved caches behave like a no-op cache, so reads miss and writes are ignored. This is common in debug mode.

```php
use Fyre\Cache\CacheManager;

$caches = app(CacheManager::class);

$default = $caches->use();
$redis = $caches->use('redis');
```

You can resolve a cache handler by key directly (see [Helpers](../core/helpers.md)):

```php
$default = cache();
$redis = cache('redis');
```

## Common operations

Cache handlers implement `Psr\SimpleCache\CacheInterface` (`get()`, `set()`, `delete()`, `clear()`, and the `*Multiple()` variants). The `Cacher` base class also provides higher-level helpers.

Examples below assume caching is enabled and the requested cache key exists.

```php
$cache = $caches->use();
// You can also do: $cache = cache();

$value = $cache->get('report.123');
$cache->set('report.123', $value, 300);

$value = $cache->remember('report.123', static fn() => buildReport(), 300);

$cache->increment('counters.reports_generated');
```

### Tagged cache entries

Use `tags()` when you want cached values to become stale after one or more tag invalidations.

```php
$users = $cache->tags('users');

$users->set('user.1', $user, 300);
$user = $users->get('user.1');

$cache->invalidateTag('users');
```

Tags can be chained or provided as an array:

```php
$activeUsers = $cache->tags('users')->tags('active');
$activeUsers->set('user.1', $user, 300);

$same = $cache->tags(['active', 'users'])->get('user.1');
```

Tag invalidation is version-based. Invalidating a tag does not eagerly delete every tagged key; instead, tagged entries become stale and are treated as cache misses on the next read.

### Cache locks

Use `synchronized()` when a callback must run while holding a named lock. The lock is released in a `finally` block, including when the callback throws.

```php
$report = $cache->synchronized(
    'reports.daily',
    static fn() => buildDailyReport(),
    expires: 30,
    wait: 2
);
```

`expires` controls the lock lifetime. `wait` controls how long to wait for another owner to release the lock; the default `0` makes a single acquisition attempt.

For manual lock management, always release an acquired lock in a `finally` block:

```php
$lock = $cache->lock('reports.daily', 30);

if (!$lock->acquire(2)) {
    throw new RuntimeException('The report is already being generated.');
}

try {
    $report = buildDailyReport();
} finally {
    $lock->release();
}
```

## Method guide

This section focuses on the methods you are most likely to use when selecting handlers and caching values.

Examples below assume `$caches` is a `CacheManager` instance, `$cache` is a `Cacher` instance, `$lock` is a `Lock` instance, and `$tagged` is a `TaggedCacher` instance.

### `CacheManager`

#### **Get a shared cache handler** (`use()`)

Returns the shared cache handler instance for a config key.

Arguments:
- `$key` (`string`): the cache config key (defaults to `default`).

```php
$default = $caches->use();
$redis = $caches->use('redis');
```

#### **Build a cache handler instance** (`build()`)

Build a one-off handler from an options array without storing it on the manager.

Arguments:
- `$options` (`array<string, mixed>`): cache options including `className`.

```php
use Fyre\Cache\Handlers\Array\ArrayCacher;

$cache = $caches->build([
    'className' => ArrayCacher::class,
    'prefix' => 'tmp_',
]);
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

#### **Add configuration at runtime** (`setConfig()`)

Stores a cache configuration under a key. The key must not already exist.

Arguments:
- `$key` (`string`): the cache config key.
- `$options` (`array<string, mixed>`): cache options for the handler.

```php
use Fyre\Cache\Handlers\File\FileCacher;

$caches->setConfig('local', [
    'className' => FileCacher::class,
    'path' => 'tmp/cache',
]);
```

#### **Check whether caching is enabled** (`isEnabled()`)

Returns whether caching is currently enabled.

```php
$enabled = $caches->isEnabled();
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

#### **Create a cache lock** (`lock()`)

Creates an owner-specific lock for a cache key.

Arguments:
- `$key` (`string`): the lock key.
- `$expires` (`int`): the lock lifetime in seconds (default: `30`).

```php
$lock = $cache->lock('reports.daily', 30);
```

#### **Run work under a lock** (`synchronized()`)

Acquires a lock, executes a callback, and releases the lock when the callback finishes.

Arguments:
- `$key` (`string`): the lock key.
- `$callback` (`Closure`): the callback to execute.
- `$expires` (`int`): the lock lifetime in seconds (default: `30`).
- `$wait` (`float`): the maximum number of seconds to wait (default: `0`).

```php
$report = $cache->synchronized(
    'reports.daily',
    static fn() => buildDailyReport()
);
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

#### **Create a tagged cache wrapper** (`tags()`)

Returns a lightweight tagged cache wrapper.

Arguments:
- `$tags` (`string|string[]`): one or more tags.

```php
$users = $cache->tags('users');
$users->set('user.1', $user, 300);
```

#### **Invalidate cache tags** (`invalidateTag()` / `invalidateTags()`)

Invalidates one or more cache tags.

Arguments:
- `$tag` (`string`): the tag to invalidate.
- `$tags` (`string[]`): the tags to invalidate.

```php
$cache->invalidateTag('users');
$cache->invalidateTags(['users', 'active']);
```

### `Lock`

#### **Acquire a lock** (`acquire()`)

Attempts to acquire the lock for this owner.

Arguments:
- `$wait` (`float`): the maximum number of seconds to wait (default: `0`).

```php
$acquired = $lock->acquire(2);
```

#### **Refresh a lock** (`refresh()`)

Extends the lifetime of an acquired lock using its configured expiration.

```php
$refreshed = $lock->refresh();
```

#### **Release a lock** (`release()`)

Releases a lock owned by this object.

```php
$released = $lock->release();
```

### `TaggedCacher`

#### **Get a tagged value** (`get()`)

Retrieves a tagged cache value, returning the default if the key is missing or any of the tag versions no longer match.

Arguments:
- `$key` (`string`): the cache key.
- `$default` (`mixed`): the default value when the tagged value is missing or stale.

```php
$user = $tagged->get('user.1');
```

#### **Set a tagged value** (`set()`)

Stores a tagged cache value together with the current tag version snapshot.

Arguments:
- `$key` (`string`): the cache key.
- `$value` (`mixed`): the value to store.
- `$expire` (`DateInterval|int|null`): time to live for this value, in seconds or as a `DateInterval`.

```php
$tagged->set('user.1', $user, 300);
```

#### **Get or compute a tagged value** (`remember()`)

Retrieves a tagged value, or computes and stores it when the tagged key is missing or stale.

Arguments:
- `$key` (`string`): the cache key.
- `$callback` (`Closure`): callback that generates the value on a miss.
- `$expire` (`DateInterval|int|null`): time to live for this value, in seconds or as a `DateInterval`.

```php
$user = $tagged->remember('user.1', static fn() => loadUser(1), 300);
```

#### **Delete a tagged value** (`delete()`)

Deletes a tagged cache value for this tag namespace.

Arguments:
- `$key` (`string`): the cache key.

```php
$tagged->delete('user.1');
```

#### **Merge additional tags** (`tags()`)

Returns a new tagged wrapper with additional tags merged into the current tag set.

Arguments:
- `$tags` (`string|string[]`): one or more tags to merge.

```php
$activeUsers = $cache->tags('users')->tags('active');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- in debug mode, caching is often disabled, so newly resolved caches act like a no-op cache
- disabling caching affects newly built handlers only; already-loaded cache instances keep behaving as before until they are rebuilt
- cache keys cannot contain `{ } ( ) / \ @ :`
- passing a zero or negative TTL to `set()` or `setMultiple()` deletes the affected entries, while `null` uses the handler's configured `expire` value
- `FileCacher` needs a writable path, and its prefix cannot contain the system directory separator
- `RedisCacher::clear()` needs a prefix unless `flushDatabase` is enabled
- invalidating a tag is lazy: tagged values become stale and disappear on the next tagged read
- `ArrayCacher` locks coordinate only with locks created by the same cacher instance, while file, Redis, and Memcached locks can coordinate across workers
- `NullCacher` locks are no-ops and do not coordinate shared work
- `synchronized()` throws a `CacheException` if it cannot acquire the lock within the configured wait time

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
