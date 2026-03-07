# Router

`Fyre\Router\Router` connects routes, matches incoming requests to a destination, and generates URLs from named route aliases.

## Table of Contents

- [Purpose](#purpose)
- [How routing works](#how-routing-works)
- [Defining routes](#defining-routes)
  - [Basic route (closure destination)](#basic-route-closure-destination)
  - [Controller route destination](#controller-route-destination)
  - [Matching by scheme/host/port](#matching-by-schemehostport)
  - [Method constraints](#method-constraints)
- [Route destinations](#route-destinations)
- [Route groups](#route-groups)
- [Path placeholders and patterns](#path-placeholders-and-patterns)
- [Aliases and URL generation](#aliases-and-url-generation)
- [Route attributes and discovery](#route-attributes-and-discovery)
  - [Example controller using `#[Route]`](#example-controller-using-route)
  - [Discovering routes with `Router::discoverRoutes()`](#discovering-routes-with-routerdiscoverroutes)
- [Method guide](#method-guide)
  - [Route definitions](#route-definitions)
  - [Route discovery](#route-discovery)
  - [Request parsing](#request-parsing)
  - [URL generation](#url-generation)
  - [Utilities](#utilities)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `Router` when you want to:

- define routes, placeholders, and groups in one place
- match inbound requests and extract placeholder values into `routeArguments`
- generate URLs from aliases so paths can change safely

## How routing works

Routing is a match-and-dispatch workflow that runs before your final handler:

- Routes can constrain matching by HTTP method, scheme, host, port, and path.
- When a route matches, placeholder values are extracted into `routeArguments`.
- The first matching route wins (routes are checked in the order you connected them).

In a typical HTTP pipeline, router middleware calls `Router::parseRequest()`, which sets `relativePath`, `route`, and `routeArguments` request attributes (see [HTTP Middleware](../http/middleware.md)).

If `App.baseUri` includes a path (for example `/subdir`), `Router::parseRequest()` strips that base path from the incoming request path before matching routes.

## Defining routes

Routes are registered with `Router::connect()`. Convenience methods exist for common HTTP verbs, but they all end up calling `connect()` with a predefined method list.

In an application, routes are typically defined in `CONFIG/routes.php` (commonly `config/routes.php`) and loaded when the router is constructed.

Most examples assume you already have a `$router` instance (for example via dependency injection). If helpers are loaded, you can also resolve it from the container:

```php
use Fyre\Router\Router;

$router = app(Router::class);
```

Examples below also assume `ServerRequestInterface` is already imported when needed.

All route paths are normalized to a leading slash with no surrounding slashes (for example, `posts/` becomes `/posts`).

### Basic route (closure destination)

```php
$router->get(
    'health',
    static fn(ServerRequestInterface $request): string => 'ok',
    as: 'health'
);
```

### Controller route destination

Controller destinations can be provided as:

- a controller class name string (defaults to the `index` action), or
- an array of `[controllerClass, action]`.

When using a controller destination array, it must contain the controller class name (not an instance).

```php
$router->get('posts', PostsController::class);
$router->get('posts/{id}', [PostsController::class, 'show'], as: 'posts.show');
```

### Matching by scheme/host/port

Scheme, host, and port constraints are optional. If provided, they must match the incoming request URI.

```php
$router->get(
    'account',
    static fn(ServerRequestInterface $request): string => 'secure area',
    scheme: 'https',
    host: 'example.com',
    as: 'account'
);
```

### Method constraints

A route can be constrained to one or more HTTP methods via the `methods:` argument:

- If `methods` is `null`, the route matches any request method.
- If `methods` is provided, methods are uppercased and de-duplicated when the route is connected.

```php
$router->connect(
    'contact',
    static fn(ServerRequestInterface $request): string => 'contact',
    methods: ['GET', 'POST'],
    as: 'contact'
);
```

## Route destinations

The router selects a route type based on how you define the destination:

- Closure destination → `ClosureRoute`
- Redirect route (`Router::redirect()` or `connect(..., redirect: true)`) → `RedirectRoute`
- Any other destination (string or array) → `ControllerRoute`

When a destination is executed, it may return a `ResponseInterface` or a string. If it returns a string, it is wrapped into a response body.

## Route groups

`Router::group()` lets you apply shared settings to multiple routes. Groups can be nested; settings cascade down to all routes connected inside the callback.

Group settings are applied in stack order (nested groups last). Middleware and placeholders are merged from outer → inner → route.

```php
use Fyre\Router\Router;

$router->group(
    static function(Router $router): void {
        $router->get(
            'status',
            static fn(ServerRequestInterface $request): string => 'ok',
            as: 'status'
        );
    },
    prefix: 'api',
    as: 'api.'
);
```

## Path placeholders and patterns

Route paths support placeholders using `{name}` syntax:

- `{id}` captures a single path segment
- `{id?}` makes the entire `/{id}` segment optional during matching
- `{post:slug}` associates a placeholder with a “binding field” name (used by binding middleware)

You can constrain placeholder values by providing a placeholder map when connecting a route (or at the group level). Keys are placeholder names and values are regular expressions (without delimiters).

```php
use Psr\Http\Message\ServerRequestInterface;

$router->get(
    'posts/{id}',
    static fn(ServerRequestInterface $request, string $id): string => 'post '.$id,
    placeholders: [
        'id' => '\d+',
    ],
    as: 'posts.show'
);
```

When a route matches, extracted arguments are stored on the request as `routeArguments`. Argument keys are derived from the placeholder names in the route path (optional `?` and any `:field` suffix are not included).

For optional placeholders like `{id?}`, use an argument key of `id` (without `?`) for both matching and URL generation.

## Aliases and URL generation

When you connect a route with `as: 'name'`, the router registers the route as an alias. You can then generate URLs with `Router::url()`.

Special argument keys:

- `?` for query parameters (passed as an array)
- `#` for the URI fragment

For URL generation details (including base URI handling), see [URL Generation](url-generation.md).

For how the matched route is dispatched (including route-specific middleware), see [Route Handler](route-handler.md).

```php
use Psr\Http\Message\ServerRequestInterface;

$router->get(
    'posts/{id}',
    static fn(ServerRequestInterface $request, string $id): string => 'post '.$id,
    placeholders: ['id' => '\d+'],
    as: 'posts.show'
);

$url = $router->url('posts.show', [
    'id' => 42,
    '?' => ['page' => 2],
    '#' => 'comments',
]);
```

## Route attributes and discovery

If you prefer controller methods to “just become routes”, you can use route discovery: define routing metadata with `#[Route]` attributes, then ask the router to discover and connect routes for one or more namespaces.

In addition to `#[Route(...)]`, you can use method attributes like `#[Get]`, `#[Post]`, `#[Put]`, `#[Patch]`, and `#[Delete]` to define the HTTP method constraint without explicitly passing a `methods` list.

For the full discovery rules (conventions, overrides, hiding actions, caching), see [Route Discovery](route-discovery.md).

### Example controller using `#[Route]`

```php
use Fyre\Router\Attributes\Post;
use Fyre\Router\Attributes\Route;

#[Route('posts', as: 'posts')]
class PostsController
{
    public function index(): string
    {
        return '';
    }

    #[Post]
    public function create(): string
    {
        return '';
    }
}
```

### Discovering routes with `Router::discoverRoutes()`

`Router::discoverRoutes()` delegates to `RouteLocator::discover()`, then calls `connect()` for each discovered route definition (returned routes are sorted most-specific path first).

```php
$router->discoverRoutes(['Your\Controllers']);
```

## Method guide

This section focuses on the methods you’ll use most when defining routes, parsing requests, and generating URLs.

Most examples assume you already have a `$router` instance (see [Defining routes](#defining-routes)).

### Route definitions

#### **Connect a route** (`connect()`)

Connect a route path to a destination, optionally constraining scheme/host/port/methods, attaching route-specific middleware, and registering an alias for URL generation.

Arguments:
- `$path` (`string`): the route path (normalized before use).
- `$destination` (`array|Closure|string`): the destination (closure, controller destination, or redirect target when `redirect` is enabled).
- `$scheme` (`string|null`): restrict matching to a URI scheme.
- `$host` (`string|null`): restrict matching to a host (supports `*` wildcards).
- `$port` (`int|null`): restrict matching to a port.
- `$methods` (`string[]|null`): restrict matching to a set of HTTP methods (or `null` to match any).
- `$middleware` (`array`): route middleware entries (executed by [Route Handler](route-handler.md)).
- `$placeholders` (`array`): placeholder patterns (regex strings without delimiters).
- `$as` (`string|null`): alias name for URL generation.
- `$redirect` (`bool`): whether to create a redirect route.

```php
$router->connect(
    'posts/{id}',
    [PostsController::class, 'show'],
    methods: ['GET'],
    placeholders: ['id' => '\d+'],
    as: 'posts.show'
);
```

#### **Connect common HTTP verb routes** (`get()`, `post()`, `put()`, `patch()`, `delete()`)

Convenience wrappers around `connect()` that pre-fill the `methods` argument.

```php
use Psr\Http\Message\ServerRequestInterface;

$router->post(
    'contact',
    static fn(ServerRequestInterface $request): string => 'submitted',
    as: 'contact.submit'
);
```

#### **Connect a redirect route** (`redirect()`)

Create a route that issues an HTTP redirect response when matched.

Arguments:
- `$path` (`string`): the route path.
- `$destination` (`string`): the redirect destination (may include `{placeholders}` substituted from the matched route arguments).
- `$scheme` (`string|null`): restrict matching to a URI scheme.
- `$host` (`string|null`): restrict matching to a host.
- `$port` (`int|null`): restrict matching to a port.
- `$methods` (`string[]|null`): restrict matching to a set of HTTP methods (or `null` to match any).
- `$middleware` (`array`): route middleware entries.
- `$placeholders` (`array`): placeholder patterns for matching.
- `$as` (`string|null`): alias name for URL generation.

```php
$router->redirect('old-posts/{id}', '/posts/{id}');
```

#### **Group routes** (`group()`)

Apply shared options (prefix, constraints, middleware, placeholders, alias prefix) to all routes connected inside the callback.

Arguments:
- `$callback` (`Closure`): callback invoked to connect the group’s routes.
- `$prefix` (`string|null`): prefix path applied to each route in the group.
- `$scheme` (`string|null`): scheme applied to routes in the group (unless overridden).
- `$host` (`string|null`): host applied to routes in the group (unless overridden).
- `$port` (`int|null`): port applied to routes in the group (unless overridden).
- `$middleware` (`array`): middleware merged into routes in the group.
- `$placeholders` (`array`): placeholder patterns merged into routes in the group.
- `$as` (`string|null`): alias prefix concatenated onto route aliases in the group.

```php
use Fyre\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

$router->group(
    static function(Router $router): void {
        $router->get(
            'status',
            static fn(ServerRequestInterface $request): string => 'ok',
            as: 'status'
        );
    },
    prefix: 'api',
    as: 'api.'
);
```

#### **Clear routes** (`clear()`)

Remove all connected routes and alias mappings.

```php
$router->clear();
```

### Route discovery

#### **Load discovered routes** (`discoverRoutes()`)

Discover and connect routes using `RouteLocator` (used by [Route Discovery](route-discovery.md)).

Arguments:
- `$namespaces` (`string[]`): namespaces to scan.

```php
$router->discoverRoutes(['Your\Controllers']);
```

### Request parsing

#### **Parse a request** (`parseRequest()`)

Match the incoming request against connected routes and return a new request with routing attributes.

Throws `NotFoundException` if no connected route matches the request.

Arguments:
- `$request` (`ServerRequestInterface`): the incoming request.

```php
use Psr\Http\Message\ServerRequestInterface;

$routedRequest = $router->parseRequest($request);
$route = $routedRequest->getAttribute('route');
$args = $routedRequest->getAttribute('routeArguments');
```

### URL generation

#### **Generate a URL** (`url()`)

Generate a URL for a named route alias, substituting placeholder values and applying base URI behavior.

Throws `RouterException` if the alias does not exist, required placeholder values are missing, or a placeholder value does not match the route’s pattern.

Arguments:
- `$name` (`string`): the route alias.
- `$arguments` (`array`): placeholder values and special keys (`?` and `#`).
- `$scheme` (`string|null`): override the scheme used for URL generation.
- `$host` (`string|null`): override the host used for URL generation.
- `$port` (`int|null`): override the port used for URL generation.
- `$full` (`bool|null`): whether to force a full (absolute) URL.

```php
$url = $router->url('posts.show', [
    'id' => 42,
    '?' => ['page' => 2],
    '#' => 'comments',
]);
```

#### **Read the configured base URI** (`getBaseUri()`)

Return the router’s configured base URI string (from `App.baseUri`).

```php
$baseUri = $router->getBaseUri();
```

### Utilities

#### **Normalize a path** (`normalizePath()`)

Normalize a path to have a leading slash and no surrounding slashes. Duplicate slashes inside the path are not collapsed.

Arguments:
- `$path` (`string`): the path to normalize.

```php
use Fyre\Router\Router;

$path = Router::normalizePath('posts/42/');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Route matching is order-dependent: the first match wins.
- Route matching uses normalized paths, but duplicate slashes inside the path are not collapsed.
- Group alias prefixes are concatenated directly (no separator is inserted), so include your own separator (for example `api.`) if needed.
- Optional placeholders (`{id?}`) make the entire `/{id}` segment optional during matching, and the extracted argument key is `id` (not `id?`).
- `Router::url()` uses the base placeholder name for argument lookup (for example `['id' => 123]` for `{id?}`).
- Host matching supports `*` wildcards (for example `*.example.com`).

## Related

- [Routing](index.md)
- [URL Generation](url-generation.md)
- [Route Handler](route-handler.md)
- [Route Bindings](route-bindings.md)
- [Route Discovery](route-discovery.md)
- [HTTP Middleware](../http/middleware.md)
