# HTTP Middleware

Use middleware to run shared request logic before and after your main route or handler runs.

The two main pieces are:

- `MiddlewareQueue`, which stores middleware entries in order
- `MiddlewareRegistry`, which maps aliases and groups to middleware

## Table of Contents

- [Start here](#start-here)
- [Defining the middleware queue](#defining-the-middleware-queue)
- [Built-in middleware](#built-in-middleware)
  - [Default middleware aliases (`Engine`)](#default-middleware-aliases-engine)
  - [Other built-in middleware](#other-built-in-middleware)
- [Custom aliases and groups](#custom-aliases-and-groups)
- [String aliases and inline arguments](#string-aliases-and-inline-arguments)
- [Method guide](#method-guide)
  - [`MiddlewareQueue`](#middlewarequeue)
  - [`MiddlewareRegistry`](#middlewareregistry)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Middleware is the right tool when you want logic to run consistently around request handling:

- normalize or validate incoming requests
- attach request attributes such as auth or route context
- enforce shared rules such as CSRF, rate limiting, or CSP
- short-circuit the request with an early response

## Defining the middleware queue

In a typical application, define the queue by overriding `Engine::middleware()` (see [Engine](../core/engine.md)).

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

When ordering middleware, place middleware that creates context before middleware that depends on that context. For example, `bindings` depends on route information, so it must run after `router`.

A queue entry can be:

- a middleware instance
- a callable middleware closure
- a string alias or class name resolved by `MiddlewareRegistry`

## Built-in middleware

Fyre includes a small set of built-in HTTP middleware. Some are available as default aliases through `Engine` (see [Engine](../core/engine.md)); others can be mapped manually if you want to use them in your own queues.

### Default middleware aliases (`Engine`)

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
- `bindings` → `Fyre\Router\Middleware\SubstituteBindingsMiddleware`: resolves route parameters through custom callbacks or automatic entity and enum binding, and throws a not-found exception when a value cannot be resolved.

The default error renderer follows `App.debug`. When debugging is disabled or not configured,
unexpected exceptions produce a generic `500 Internal Server Error` body without exception details.
When debugging is enabled, the escaped exception and stack trace are included in the response.
A custom `Error.renderer` replaces this default behavior and is responsible for controlling what it exposes.

Registering `ErrorHandler` converts non-suppressed PHP errors into `ErrorException` instances. Keep
the `error` middleware near the start of the queue so errors thrown during request processing pass
through the same exception-rendering pipeline.

For deeper topic documentation, see [Authentication](../auth/authentication.md), [Authorization](../auth/authorization.md), [Auth Middleware](../auth/middleware.md), [CSRF](../security/csrf.md), [Content Security Policy (CSP)](../security/csp.md), [Router](../routing/router.md), and [Route Bindings](../routing/route-bindings.md).

### Other built-in middleware

- `Fyre\Security\Middleware\RateLimiterMiddleware` (not mapped by default): enforces request limits and can add rate-limit headers to the response (see [Rate Limiting](../security/rate-limiting.md)).

## Custom aliases and groups

Most applications use string entries in the queue:

- a middleware alias such as `session`
- a group alias such as `web`
- a middleware class name

Register your own aliases with `MiddlewareRegistry::map()` and define groups with `MiddlewareRegistry::group()`:

```php
use Fyre\Http\MiddlewareQueue;

// Assume $registry is a MiddlewareRegistry instance.
$registry->group('web', [
    'session',
    'csrf',
    'auth',
]);

$queue = new MiddlewareQueue()
    ->add('web');
```

## String aliases and inline arguments

String middleware entries can include inline arguments using the format `alias:arg1,arg2`.

Arguments are always passed as strings. They are not trimmed or type-cast automatically.

This is commonly used for middleware that accepts optional parameters, such as:

- authorization checks (for example `can:admin`)
- rate limiting overrides (for example `throttle:120,60,2`)

```php
use Fyre\Http\MiddlewareQueue;

$queue = new MiddlewareQueue()
    ->add('throttle:120,60,2');
```

The string prefix (for example `throttle`) must be a mapped alias or a resolvable class name.

## Method guide

This section focuses on the methods you are most likely to use when defining and resolving middleware.

You can also resolve it from the container (see [Helpers](../core/helpers.md)):

```php
use Fyre\Http\MiddlewareRegistry;

$registry = app(MiddlewareRegistry::class);
```

### `MiddlewareQueue`

#### **Append middleware** (`add()`)

Appends a middleware entry to the end of the queue.

Arguments:
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): a middleware instance, callable middleware, a registry alias, or a middleware class name.

```php
use Fyre\Http\MiddlewareQueue;

$queue = new MiddlewareQueue()
    ->add('session')
    ->add('router');
```

#### **Prepend middleware** (`prepend()`)

Adds a middleware entry to the start of the queue.

Arguments:
- `$middleware` (`Closure|Psr\Http\Server\MiddlewareInterface|string`): the middleware entry.

```php
use Fyre\Http\MiddlewareQueue;

$queue = new MiddlewareQueue()
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

$queue = new MiddlewareQueue()
    ->add('router')
    ->add('bindings')
    ->insertAt(1, 'auth');
```

### `MiddlewareRegistry`

#### **Map an alias** (`map()`)

Maps a string alias to middleware, so you can reference it in the queue by name.

Arguments:
- `$alias` (`string`): the alias name.
- `$middleware` (`Closure|string`): a middleware class name, or a container-invoked factory closure.
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

Returns the middleware registered for the given alias.

Arguments:
- `$alias` (`string`): the alias name.

```php
$authMiddleware = $registry->use('auth');
```

## Behavior notes

A few behaviors are worth keeping in mind:

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
