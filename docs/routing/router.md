# Router

Use `Fyre\Router\Router` to define routes, match requests, and generate URLs from route aliases.

## Table of Contents

- [Start here](#start-here)
- [Defining routes](#defining-routes)
  - [Basic route (closure destination)](#basic-route-closure-destination)
  - [Controller route destination](#controller-route-destination)
  - [Matching by scheme/host/port](#matching-by-schemehostport)
  - [Method constraints](#method-constraints)
- [Route destinations](#route-destinations)
- [Route groups](#route-groups)
- [Path placeholders and patterns](#path-placeholders-and-patterns)
- [Matching requests](#matching-requests)
- [Aliases and URL generation](#aliases-and-url-generation)
- [Route attributes and discovery](#route-attributes-and-discovery)
  - [Example controller using `#[Route]`](#example-controller-using-route)
  - [Discovering routes with `Router::discoverRoutes()`](#discovering-routes-with-routerdiscoverroutes)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use `Router` when you want to:

- define routes, placeholders, and groups in one place
- match incoming requests to a handler
- generate URLs from aliases so paths can change safely

## Defining routes

Routes are registered with `Router::connect()`. Convenience methods exist for common HTTP verbs, but they all end up calling `connect()` with a predefined method list.

In an application, routes are typically defined in `CONFIG/routes.php` (commonly `config/routes.php`). When using `Engine`, this file is loaded if it exists when the `Router` singleton is first resolved.

Most examples assume you already have a `$router` instance (for example via dependency injection). You can also resolve it from the container:

```php
use Fyre\Router\Router;

$router = app(Router::class);
```

All route paths are normalized to exactly one leading slash and no trailing slash (for example, `posts/` becomes `/posts`).

Call `clear()` to remove every connected route and alias when the router needs to be rebuilt.

### Basic route (closure destination)

```php
use Psr\Http\Message\ServerRequestInterface;

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

- If `methods` is `null`, the route accepts Fyre's default methods: `CONNECT`, `DELETE`, `GET`, `HEAD`, `OPTIONS`, `PATCH`, `POST`, `PUT`, and `TRACE`.
- If `methods` is provided, methods are uppercased and de-duplicated when the route is connected.
- Extension methods are supported when they are listed explicitly.
- A `HEAD` request falls back to a matching `GET` route when no explicit `HEAD` route matches.
- If the path matches but the method does not, the router throws a `MethodNotAllowedException`
  with every permitted method in the `Allow` header.

```php
$router->connect(
    'contact',
    static fn(ServerRequestInterface $request): string => 'contact',
    methods: ['GET', 'POST'],
    as: 'contact'
);
```

`OPTIONS` responses are not generated automatically. Register one explicitly when needed:

```php
use Fyre\Http\ClientResponse;

$router->connect(
    'contact',
    static fn(): ClientResponse => new ClientResponse([
        'statusCode' => 204,
        'headers' => [
            'Allow' => 'GET, HEAD, POST, OPTIONS',
        ],
    ]),
    methods: ['OPTIONS']
);
```

## Route destinations

The router selects a route type based on how you define the destination:

- Closure destination → `ClosureRoute`
- Redirect route (`Router::redirect()` or `connect(..., redirect: true)`) → `RedirectRoute`
- Any other destination (string or array) → `ControllerRoute`

When a destination runs, it may return a `ResponseInterface` or a string. If it returns a string, it is wrapped into a response body.

Use `redirect()` when a route should redirect to another path. Placeholders in the destination are replaced with values from the matched route.

```php
$router->redirect('old-posts/{id}', '/posts/{id}');
```

## Route groups

`Router::group()` lets you apply shared settings to multiple routes. Groups can be nested; settings cascade down to all routes connected inside the callback.

Group settings are applied in stack order (nested groups last). Middleware, placeholders, and binding callbacks are merged from outer → inner → route.

When binding callbacks use the same parameter name, inner groups override outer groups and callbacks defined directly on the route take precedence. See [Route Bindings](route-bindings.md#custom-binding-callbacks) for callback behavior and examples.

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

## Path placeholders and patterns

Route paths support placeholders using `{name}` syntax:

- `{id}` captures a single path segment
- `{name}.{extension}` places multiple required placeholders within one segment
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

## Matching requests

Use `parseRequest()` to match an incoming request. It returns a new request containing the matched `route` and `routeArguments` attributes.

```php
$routedRequest = $router->parseRequest($request);
$route = $routedRequest->getAttribute('route');
$arguments = $routedRequest->getAttribute('routeArguments');
```

The router throws a `NotFoundException` when no path matches. When the path matches but the method does not, it throws a `MethodNotAllowedException` with the permitted methods in the `Allow` header.

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

`url()` throws a `RouterException` when the alias does not exist, a required placeholder is missing, or a value does not match its placeholder pattern.

## Route attributes and discovery

If you prefer controller methods to become routes automatically, use route discovery: define routing metadata with `#[Route]` attributes, then ask the router to discover and connect routes for one or more namespaces.

In addition to `#[Route(...)]`, you can use method attributes like `#[Get]`, `#[Post]`, `#[Put]`, `#[Patch]`, and `#[Delete]` to define the HTTP method constraint without explicitly passing a `methods` list.

For the full discovery rules, see [Route Discovery](route-discovery.md).

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

Use `discoverRoutes()` when you want to register routes from controller attributes instead of writing them by hand.

```php
$router->discoverRoutes(['Your\Controllers']);
```

## Behavior notes

- Route matching is order-dependent: the first match wins.
- Route matching uses normalized paths, but duplicate slashes inside the path are not collapsed.
- Group alias prefixes are concatenated directly (no separator is inserted), so include your own separator (for example `api.`) if needed.
- Optional placeholders (`{id?}`) make the entire `/{id}` segment optional during matching, and the extracted argument key is `id` (not `id?`).
- Missing optional placeholders are initially stored as `null`. Binding middleware applies declared parameter defaults, preserves `null` for required nullable parameters, and rejects required non-nullable parameters.
- Optional placeholders must occupy an entire path segment.
- `Router::url()` uses the base placeholder name for argument lookup (for example `['id' => 123]` for `{id?}`).
- Host matching supports `*` wildcards (for example `*.example.com`).
- If `App.baseUri` includes a path such as `/subdir`, the router removes that path before matching requests and adds it back when generating URLs.

## Related

- [Routing](index.md)
- [URL Generation](url-generation.md)
- [Route Handler](route-handler.md)
- [Route Bindings](route-bindings.md)
- [Route Discovery](route-discovery.md)
- [HTTP Middleware](../http/middleware.md)
- [HTTP Requests](../http/requests.md)
