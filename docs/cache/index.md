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
- [API summary](#api-summary)
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

Call `refresh()` during long-running manual work to extend a lock's lifetime. It succeeds only while this object still owns the lock.

## API summary

### Cache manager

| Method | Purpose |
| --- | --- |
| `use($key = 'default')` | get the shared handler for a configuration |
| `build($options)` | build a one-off handler without storing it |
| `getConfig($key = null)` | read one configuration, or all configurations |
| `hasConfig($key = 'default')` | check whether a configuration exists |
| `isLoaded($key = 'default')` | check whether a handler has been built |
| `setConfig($key, $options)` | add a runtime configuration |
| `unload($key = 'default')` | remove a configuration and its loaded handler |
| `clear()` | remove every configuration and loaded handler |
| `enable()` / `disable()` | control whether newly built handlers cache values |
| `isEnabled()` | check the current manager state |

### Cache operations

| Method | Purpose |
| --- | --- |
| `remember($key, $callback, $expire = null)` | read a value or compute and store it on a miss |
| `increment($key, $amount = 1)` / `decrement($key, $amount = 1)` | change a numeric value |
| `tags($tags)` | create a tagged cache wrapper |
| `invalidateTag($tag)` / `invalidateTags($tags)` | make tagged entries stale |
| `lock($key, $expires = 30)` | create an owner-specific lock |
| `synchronized($key, $callback, $expires = 30, $wait = 0)` | run a callback while holding a lock |

`TaggedCacher` supports the normal `get()`, `set()`, `delete()`, and `remember()` operations within its tag namespace. Calling `tags()` on it returns a new wrapper with the additional tags merged in.

## Behavior notes

- In debug mode, caching is often disabled, so newly resolved caches act like a no-op cache.
- Disabling caching affects newly built handlers only; already-loaded cache instances keep behaving as before until they are rebuilt.
- Cache keys cannot contain `{ } ( ) / \ @ :`.
- Passing a zero or negative TTL to `set()` or `setMultiple()` deletes the affected entries, while `null` uses the handler's configured `expire` value.
- `FileCacher` needs a writable path, and its prefix cannot contain the system directory separator.
- `RedisCacher::clear()` needs a prefix unless `flushDatabase` is enabled.
- Invalidating a tag is lazy: tagged values become stale and disappear on the next tagged read.
- `ArrayCacher` locks coordinate only with locks created by the same cacher instance, while file, Redis, and Memcached locks can coordinate across workers.
- `NullCacher` locks are no-ops and do not coordinate shared work.
- `synchronized()` throws a `CacheException` if it cannot acquire the lock within the configured wait time.

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
