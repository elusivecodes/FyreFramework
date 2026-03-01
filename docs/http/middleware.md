# HTTP Middleware

`Fyre\Http\MiddlewareQueue` stores middleware entries for an ordered pipeline. `MiddlewareRegistry` resolves aliases and groups into executable middleware for `RequestHandler`.

## Table of Contents

- [Purpose](#purpose)
- [Defining the middleware queue](#defining-the-middleware-queue)
- [Middleware pipeline model](#middleware-pipeline-model)
- [Built-in middleware](#built-in-middleware)
  - [Default middleware aliases (Engine)](#default-middleware-aliases-engine)
  - [Other built-in middleware](#other-built-in-middleware)
- [Aliases and groups](#aliases-and-groups)
- [String aliases and inline arguments](#string-aliases-and-inline-arguments)
- [Method guide](#method-guide)
  - [MiddlewareQueue](#middlewarequeue)
  - [MiddlewareRegistry](#middlewareregistry)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Middleware is used when you want logic to run consistently around request handling:

- validate or normalize incoming requests before business logic runs
- attach attributes (for example, resolved identity or route context) to the request
- enforce security boundaries (CSRF, rate limiting, CSP headers)
- short-circuit the request with an early response (for example, “unauthenticated”)

## Defining the middleware queue

In a typical application, the middleware queue is defined by overriding `Engine::middleware()` (see [Engine](../core/engine.md)). `Engine` constructs a scoped `MiddlewareQueue`, passes it through `Engine::middleware()` for customization, then the resulting queue is executed by `RequestHandler`.

```php
use Fyre\Core\Engine;
use Fyre\Http\MiddlewareQueue;

class Application extends Engine
{
    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue
            ->add('error')
            ->add('session')
            ->add('auth')
            ->add('router')
            ->add('bindings');
    }
}
```

When ordering middleware, prefer to place middleware that creates request context before middleware that depends on it. For example, `bindings` relies on routing attributes (so it must run after `router`).

## Middleware pipeline model

🧠 The pipeline is built from three pieces:

- `MiddlewareQueue` stores middleware entries in order.
- `MiddlewareRegistry` resolves string entries into executable middleware (aliases, groups, and optional inline arguments).
- `RequestHandler` executes the queue, calling each middleware with the request and a handler for “the next step” (see [Request Handler](request-handler.md)).

A queue entry can be any of:

- a PSR-15 `MiddlewareInterface` instance
- a callable (usually a `Closure`) with the shape `function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface`
- a string alias (or class name) that can be resolved by `MiddlewareRegistry`

Requests and responses are standard PSR-7 messages; middleware reads data from the request and can influence the response returned by downstream handlers (see [HTTP Requests](requests.md) and [HTTP Responses](responses.md)).

## Built-in middleware

Fyre includes a small set of built-in HTTP middleware. Some are pre-mapped as aliases by `Engine` via the default `MiddlewareRegistry` (see [Engine](../core/engine.md)); others can be mapped manually if you want to use them in your own queues.

### Default middleware aliases (Engine)

These aliases are mapped by default:

- `error` → `Fyre\Core\Middleware\ErrorHandlerMiddleware`: catches any thrown `Throwable` and delegates to `ErrorHandler::render()`.
- `session` → `Fyre\Http\Middleware\SessionMiddleware`: starts a session, exposes it as the `session` request attribute, and closes it after the handler returns.
- `auth` → `Fyre\Auth\Middleware\AuthMiddleware`: runs authenticators, adds `auth` and `user` request attributes, then calls `beforeResponse()` on authenticators after the handler runs.
- `authenticated` → `Fyre\Auth\Middleware\AuthenticatedMiddleware`: requires a logged-in user; redirects HTML requests to the login URL and throws for JSON requests.
- `unauthenticated` → `Fyre\Auth\Middleware\UnauthenticatedMiddleware`: requires a logged-out user; throws a not-found exception when already authenticated.
- `can` → `Fyre\Auth\Middleware\AuthorizedMiddleware`: checks an authorization rule via `Auth::access()->allows(...)` and either continues, redirects, or throws depending on request type and authentication state.
- `csrf` → `Fyre\Security\Middleware\CsrfProtectionMiddleware`: enforces CSRF token checks and applies CSRF response behavior via `beforeResponse()`.
- `csp` → `Fyre\Security\Middleware\CspMiddleware`: applies CSP headers to the response returned by the next handler.
- `router` → `Fyre\Router\Middleware\RouterMiddleware`: parses the request through the router and sets route attributes like `relativePath`, `route`, and `routeArguments`.
- `bindings` → `Fyre\Router\Middleware\SubstituteBindingsMiddleware`: substitutes route parameters with bound values (for example, ORM entities) and throws a not-found exception when a binding cannot be resolved.

For deeper topic documentation, see [Authentication](../auth/authentication.md), [Authorization](../auth/authorization.md), [Auth Middleware](../auth/middleware.md), [CSRF](../security/csrf.md), [Content Security Policy (CSP)](../security/csp.md), and [Router](../routing/router.md).

### Other built-in middleware

- `Fyre\Security\Middleware\RateLimiterMiddleware` (not mapped by default): enforces request rate limits and can add rate limit headers to responses; when the limit is exceeded it throws `TooManyRequestsException` with a `Retry-After` header (see [Rate Limiting](../security/rate-limiting.md)).

## Aliases and groups

In practice, most applications build middleware queues using string entries:

- a **middleware alias** (like `session` or `csrf`)
- a **group alias** (like `web`, which expands to multiple middleware)
- a **middleware class name** (resolved through the container)

These are resolved at runtime by `MiddlewareRegistry` and executed by `RequestHandler`.

Aliases and groups can be defined by calling:

- `MiddlewareRegistry::map()` for single middleware aliases
- `MiddlewareRegistry::group()` for groups that expand to a list

When a group alias is invoked, it runs as its own sub-queue, and the handler passed into the group becomes the “fallback handler” after the group finishes.

In `Engine`, a shared `MiddlewareRegistry` is constructed and pre-mapped with default middleware aliases. You can add additional aliases in your `Engine::middleware()` override when needed (see [Engine](../core/engine.md)).

```php
use Fyre\Http\MiddlewareQueue;

// Assume $registry is a MiddlewareRegistry instance.
$registry->group('web', [
    'session',
    'csrf',
    'auth',
]);

$queue = (new MiddlewareQueue())
    ->add('web');
```

## String aliases and inline arguments

String middleware entries can include inline arguments using the format `alias:arg1,arg2`.

When inline arguments are present:

- the string is split at the first `:`
- the argument segment is split by `,`
- all arguments are passed through as strings (they are not trimmed or type-cast)

If the alias resolves to a PSR-15 middleware instance, it is converted to its `process()` callable before invocation. This enables middleware `process()` methods to accept optional parameters after `$handler` (for example, `process(..., string|null $limit = null)`), with inline arguments appended after `$request` and `$handler`.

This is commonly used for middleware that accepts optional parameters, such as:

- authorization checks (for example `can:admin`)
- rate limiting overrides (for example `throttle:120,60,2`)

```php
use Fyre\Http\MiddlewareQueue;

$queue = (new MiddlewareQueue())
    ->add('throttle:120,60,2');
```

📌 Note: The string prefix (for example `throttle`) must be a mapped alias (or a resolvable class name). See [`MiddlewareRegistry::map()`](#map-an-alias-map) for mapping custom aliases.

## Method guide

This section focuses on the methods you’ll use most when defining and resolving middleware.

If helpers are loaded, you can also resolve it from the container (see [Helpers](../core/helpers.md)):

```php
use Fyre\Http\MiddlewareRegistry;

$registry = app(MiddlewareRegistry::class);
```

### MiddlewareQueue

#### **Append middleware** (`add()`)

Appends a middleware entry to the end of the queue.

Arguments:
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): a middleware instance, callable middleware, a registry alias, or a middleware class name.

```php
use Fyre\Http\MiddlewareQueue;

$queue = (new MiddlewareQueue())
    ->add('session')
    ->add('router');
```

#### **Prepend middleware** (`prepend()`)

Adds a middleware entry to the start of the queue.

Arguments:
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): the middleware entry.

```php
use Fyre\Http\MiddlewareQueue;

$queue = (new MiddlewareQueue())
    ->add('router')
    ->prepend('error');
```

#### **Insert middleware at an index** (`insertAt()`)

Inserts middleware at a specific index.

Arguments:
- `$index` (`int`): the index to insert at (uses PHP `array_splice()` semantics).
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): the middleware entry.

```php
use Fyre\Http\MiddlewareQueue;

$queue = (new MiddlewareQueue())
    ->add('router')
    ->add('bindings')
    ->insertAt(1, 'auth');
```

### MiddlewareRegistry

#### **Map an alias** (`map()`)

Maps a string alias to middleware, so you can reference it in the queue by name.

Arguments:
- `$alias` (`string`): the alias name.
- `$middleware` (`Closure|string`): a PSR-15 middleware class name, or a container-invoked factory closure.
- `$arguments` (`array`): additional constructor/call arguments (when supported).

```php
use Fyre\Security\Middleware\RateLimiterMiddleware;

$registry->map('throttle', RateLimiterMiddleware::class, [
    'options' => [
        'limit' => 120,
        'window' => 60,
    ],
]);
```

#### **Define a group alias** (`group()`)

Maps an alias to a list of middleware entries. When invoked, the group runs as its own sub-queue.

Arguments:
- `$alias` (`string`): the group name.
- `$middleware` (`array`): middleware entries for the group.

```php
$registry->group('web', [
    'session',
    'csrf',
    'auth',
]);
```

#### **Resolve a middleware entry** (`resolve()`)

Resolves a middleware entry into executable middleware. This is the method that expands inline argument strings like `throttle:120,60,2`.

Arguments:
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): the middleware entry.

```php
$middleware = $registry->resolve('can:admin');
```

#### **Resolve a shared alias** (`use()`)

Builds (or returns a cached) middleware instance/callable for the given alias.

Arguments:
- `$alias` (`string`): the alias name.

```php
$authMiddleware = $registry->use('auth');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `MiddlewareQueue::current()` throws when the queue is exhausted; check `valid()` before calling it outside of `RequestHandler` execution.
- String middleware entries are resolved through `MiddlewareRegistry::use()` and cached as shared instances/callables per string; calling `map()` or `group()` clears the cached instance for that alias (this also applies when the string is a middleware class name).
- Inline arguments are not trimmed or type-cast; `can:admin` passes `"admin"` as a string argument.
- Inline argument parsing uses `:` and `,` only; an entry like `alias:` will pass a single empty-string argument.
- If you register callable middleware that requires extra parameters, ensure those parameters are optional or always provide inline arguments when referencing it as a string.

## Related

- [Engine](../core/engine.md)
- [Helpers](../core/helpers.md)
- [HTTP Requests](requests.md)
- [HTTP Responses](responses.md)
- [Request Handler](request-handler.md)
- [Sessions](sessions.md)
- [Router](../routing/router.md)
- [Authentication](../auth/authentication.md)
- [Authorization](../auth/authorization.md)
- [CSRF](../security/csrf.md)
- [Content Security Policy (CSP)](../security/csp.md)
- [Rate Limiting](../security/rate-limiting.md)
