# Sessions

Use `Fyre\Http\Session\Session` when you need state to survive across requests, such as login state, flash messages, or short-lived workflow data.

It wraps PHP sessions and adds dot-notation access, flash and temporary values, and pluggable storage handlers.

## Table of Contents

- [Start here](#start-here)
- [Using sessions in requests](#using-sessions-in-requests)
- [Session lifecycle](#session-lifecycle)
  - [Starting and closing](#starting-and-closing)
  - [Refreshing the session ID](#refreshing-the-session-id)
  - [Destroying the session](#destroying-the-session)
- [Session configuration](#session-configuration)
  - [Top-level options](#top-level-options)
  - [Example configuration](#example-configuration)
- [Session handlers](#session-handlers)
  - [File storage](#file-storage)
  - [Database storage](#database-storage)
  - [Redis storage](#redis-storage)
  - [Memcached storage](#memcached-storage)
  - [Custom handlers](#custom-handlers)
- [Method guide](#method-guide)
  - [`Session`](#session)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

A `Session` instance wraps PHP session handling and gives you a simpler application API:

- session start happens lazily (the first `get()`, `set()`, `has()`, etc. starts the session if needed), unless something starts it explicitly (for example, `SessionMiddleware`)
- values are stored under `$_SESSION` and can be addressed with dot-notation keys
- “flash” values survive for one subsequent session start, and then are automatically cleared
- “temporary” values are cleared after their expiry time when the session is started

## Using sessions in requests

`SessionMiddleware` starts a session for each request and makes it available to downstream middleware/handlers via the request attributes.

To register it in your application’s middleware queue, see [HTTP Middleware](middleware.md).

- The session is injected under the `session` attribute key.
- The session is closed after the handler returns a response (a close is attempted even when exceptions are thrown).
- For safe HTTP methods (`GET`, `HEAD`, `OPTIONS`, `TRACE`), the middleware starts a read-only session when `Session::allowReadOnly()` returns true.

After `SessionMiddleware` has run for the current request, you can access the session via request attributes:

```php
use Fyre\Http\Session\Session;
use Psr\Http\Message\ServerRequestInterface;

function handle(ServerRequestInterface $request): void
{
    $session = $request->getAttribute('session');
    if (!($session instanceof Session)) {
        return;
    }

    $userId = $session->get('user.id');
}
```

## Session lifecycle

Sessions can be started, closed, refreshed (ID regeneration), or destroyed. In HTTP requests, `SessionMiddleware` handles start/close automatically; the methods below are still useful in custom flows (for example, explicit logout).

### Starting and closing

The session starts when you call `Session::start()`, or implicitly when you access session data (for example, `get()` calls `start()` internally).

`Session::close()`:

- closes the underlying PHP session (non-CLI)
- resets the in-memory “started” state on the `Session` instance

`Session::startReadOnly()` starts the session in read-only mode and skips the write-oriented housekeeping that happens during a normal `start()`.

### Refreshing the session ID

Use `Session::refresh(bool $deleteOldSession = false): void` to regenerate the session ID (non-CLI). This is commonly done after authentication state changes.

### Destroying the session

Use `Session::destroy(): void` to destroy the current session and clear all in-memory session data.

## Session configuration

Session configuration is read from the `Session` key in your config (see [Config](../core/config.md)).

### Top-level options

- `expires` (`int|null`): idle timeout in seconds. When `null`, it defaults to PHP’s `session.gc_maxlifetime`.
- `path` (`string`): the session save path used by PHP (`session.save_path`).
  - When using `DatabaseSessionHandler`, this value is treated as the database table name.
- `allowReadOnly` (`bool`): whether read-only sessions are allowed (used by `SessionMiddleware` to pick between `start()` and `startReadOnly()`).
- `cookie` (`array`): cookie settings applied via PHP ini (`session.cookie_*` and `session.name`):
  - `name` (`string`)
  - `expires` (`int`) cookie lifetime in seconds (0 means “until the browser is closed”)
  - `domain` (`string`)
  - `path` (`string`)
  - `secure` (`bool`)
  - `sameSite` (`string`) for example `Lax`

Fyre also enables the usual safety-focused PHP session flags at runtime, such as HTTP-only cookies and strict mode.

### Example configuration

```php
use Fyre\Http\Session\Handlers\FileSessionHandler;

return [
    'Session' => [
        'expires' => 3600,
        'path' => 'tmp/sessions',
        'allowReadOnly' => true,
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

## Session handlers

By default, Fyre stores session data under the configured save path. You can switch to database, Redis, or Memcached storage through `Session.handler`.

Handlers are configured under `Session.handler`:

- `className` (`class-string<SessionHandlerInterface>`): the handler class to build and register.
- `expires` (`int`): handler expiration in seconds. Defaults to `Session.expires`.
- `prefix` (`string`): optional storage key prefix for handlers that support it.

### File storage

Implemented by `FileSessionHandler`.

Stores one file per session under `Session.path`. The stored filename is `prefix + sessionId`.

Notes:
- The session directory is created automatically when missing.
- Writes use `LOCK_EX` to reduce race conditions.
- Missing session files read as an empty string (errors are suppressed).

Common options:

- `prefix` (`string`): storage key prefix for the filename.

### Database storage

Implemented by `DatabaseSessionHandler`.

Stores session rows in a database table. `Session.path` is treated as the table name.

Common options:

- `connectionKey` (`string`): the database connection key to use (defaults to `default`).

The table must have at least:

- `id` (session id)
- `data` (session payload)
- `created`
- `modified`

Expired sessions are removed by comparing `modified` against the session lifetime.

### Redis storage

Implemented by `RedisSessionHandler`.

Stores session payloads in Redis, with a TTL based on `expires`.

Notes:
- The handler ignores `Session.path`.
- Redis TTL handles expiration internally; explicit garbage collection is not performed by the handler.

Common options:

- `host` (`string`)
- `port` (`int`)
- `password` (`string|null`)
- `database` (`int|null`)
- `timeout` (`int`)
- `tls` (`bool`)
- `ssl` (`array`):
  - `key` (`string|null`)
  - `cert` (`string|null`)
  - `ca` (`string|null`)
- `prefix` (`string`)

### Memcached storage

Implemented by `MemcachedSessionHandler`.

Stores session payloads in Memcached, with an expiration time based on `expires`.

Notes:
- The handler ignores `Session.path`.
- Memcached handles expiration internally; explicit garbage collection is not performed by the handler.

Common options:

- `host` (`string`)
- `port` (`int`)
- `weight` (`int`)
- `prefix` (`string`)

### Custom handlers

If you build a custom handler, extending the framework's `SessionHandler` base class gives you default config merging and a helper for prefixed storage keys.

## Method guide

This section documents the session APIs you’ll use most often in application code.

Most examples assume you already have a `$session` instance (via dependency injection or request attributes).

You can also use the `session()` helper (see [Helpers](../core/helpers.md)):

- `session()` returns the `Session` instance.
- `session($key)` reads a value from the session.
- `session($key, $value)` writes a value to the session.

In HTTP requests, this is typically the same `Session` instance started by `SessionMiddleware`. The read/write forms start the session lazily (because they call `Session::get()` and `Session::set()` internally).

### `Session`

#### **Start the session** (`start()`)

Starts the underlying PHP session if it hasn’t already been started.

In CLI, this initializes `$_SESSION` (if needed) and uses a fixed session id (`cli`).

```php
$session->start();
```

#### **Start the session (read-only)** (`startReadOnly()`)

Starts the session in read-only mode (PHP’s `read_and_close=true`) and marks the `Session` instance as read-only.

`startReadOnly()` does not enforce `allowReadOnly()`.

```php
$session->startReadOnly();
$userId = $session->get('user.id');
```

#### **Read a value** (`get()`)

Reads a value using dot-notation keys, returning `$default` when missing.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).
- `$default` (`mixed`): the value to return when the key is missing.

```php
$userId = $session->get('user.id');
```

#### **Check whether a key exists** (`has()`)

Returns `true` when a key exists.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).

```php
$hasUser = $session->has('user.id');
```

#### **Write a value** (`set()`)

Sets a value using dot-notation keys.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).
- `$value` (`mixed`): the value to set.

```php
$session->set('user.id', 123);
```

#### **Delete a value** (`delete()`)

Deletes a value using dot-notation keys.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).

```php
$session->delete('user.id');
```

#### **Read and delete a value** (`consume()`)

Returns the value for `$key` and then deletes it.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).

```php
$notice = $session->consume('notice');
```

#### **Set a flash value** (`setFlash()`)

Sets a value that is automatically rotated after the next session start.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).
- `$value` (`mixed`): the value to set.

```php
$session->setFlash('notice', 'Saved.');
```

#### **Set a temporary value** (`setTemp()`)

Sets a value with an expiry time in seconds. Expired values are removed when the session is started.

Arguments:
- `$key` (`string`): the session key (dot-notation supported).
- `$value` (`mixed`): the value to set.
- `$expire` (`int`): expiry time in seconds (defaults to `300`).

```php
$session->setTemp('mfa.challenge', 'pending', 300);
```

#### **Clear session data** (`clear()`)

Clears all session data.

```php
$session->clear();
```

#### **Close the session** (`close()`)

Closes the underlying PHP session and resets the `Session` started/read-only flags.

```php
$session->close();
```

#### **Regenerate the session id** (`refresh()`)

Regenerates the session id (non-CLI). This is commonly done after authentication state changes.

Arguments:
- `$deleteOldSession` (`bool`): whether to delete the old session data.

```php
$session->refresh(true);
```

#### **Destroy the session** (`destroy()`)

Destroys the current session and clears all in-memory session data.

```php
$session->destroy();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `startReadOnly()` does not enforce `allowReadOnly()`, so check `allowReadOnly()` when choosing to start read-only mode outside of `SessionMiddleware`.
- Writing methods throw a `SessionException` when the session is started in read-only mode.
- `startReadOnly()` does not update activity tracking, rotate flash values, or clear expired temporary values; these happen during `start()`.
- The default session cookie is marked `Secure`, so browsers will not send it over plain HTTP.
- When a session has expired, Fyre clears it and starts a new one on the next request. Use `refresh()` after authentication state changes when you want a new session ID as well.

## Related

- [HTTP Middleware](middleware.md)
- [HTTP Requests](requests.md)
- [Auth](../auth/index.md)
