# Route Discovery

`Fyre\Router\Router::discoverRoutes()` converts controller classes into routes by reading route attributes and applying conventions for paths, HTTP methods, and aliases.

## Table of Contents

- [Purpose](#purpose)
- [Route attributes](#route-attributes)
  - [HTTP method attributes](#http-method-attributes)
  - [The `Route` attribute](#the-route-attribute)
  - [Hiding controllers and actions](#hiding-controllers-and-actions)
- [Conventions](#conventions)
  - [Default path](#default-path)
  - [Default methods](#default-methods)
  - [Default alias](#default-alias)
  - [Controller defaults and method overrides](#controller-defaults-and-method-overrides)
- [Caching](#caching)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use route discovery when you want controller methods to become routes automatically:

- define routes with `#[Route]` and method attributes like `#[Get]` / `#[Post]`
- rely on conventions for paths, methods, and aliases when you don’t want to specify everything
- optionally cache discovered routes per namespace

Discovery is typically run through `Router::discoverRoutes()`, which delegates to `RouteLocator::discover()` and then connects each discovered route.

```php
$router->discoverRoutes([
    'Your\Controllers',
]);
```

For routing basics, see [Router](router.md). For URL generation, see [URL Generation](url-generation.md).

Internally, discovery produces route definition arrays that match `Router::connect()` argument names, so they can be connected using named argument unpacking.

When discovering routes for a namespace, the locator:

1. Scans PHP files under folders registered for the namespace.
2. Builds a class name from the namespace and file path and skips classes that are not loadable.
3. Skips abstract classes.
4. Treats each public method as a route candidate.
5. Reads route metadata from attributes and fills in missing pieces using conventions.

## Route attributes

Route discovery reads the first attribute on a controller class and on each controller method that is an instance of `Fyre\Router\Attributes\Route`. Because attributes are read in “instance-of” mode, any attribute class that extends `Route` can supply route metadata.

### HTTP method attributes

For the common case of “one method → one HTTP verb”, the router provides method-specific attributes:

- `Get`
- `Post`
- `Put`
- `Patch`
- `Delete`

All of these attributes live in `Fyre\Router\Attributes`.

These attributes can be applied to a controller class or a controller method. They support the same parameters as `Route`, except the `methods` value is fixed to a single HTTP method.

When routes are connected, paths are normalized to a leading `/`, so attributes may use either `'posts'` or `'/posts'`. Examples in this page omit the leading `/`.

```php
use Fyre\Router\Attributes\Get;
use Fyre\Router\Attributes\Hidden;
use Fyre\Router\Attributes\Post;
use Fyre\Router\Attributes\Route;

#[Route('posts', as: 'posts')]
class PostsController
{
    #[Get]
    public function index(): string
    {
        return '';
    }

    #[Get('posts/{post}', as: 'posts.show')]
    public function show(string $post): string
    {
        return '';
    }

    #[Post]
    public function create(): string
    {
        return '';
    }

    #[Hidden]
    public function internalHealthCheck(): string
    {
        return '';
    }
}
```

### The `Route` attribute

Use `Fyre\Router\Attributes\Route` when you want to set `methods` explicitly (including multiple methods), or when you need route metadata that doesn’t map cleanly to a single-verb attribute.

`Route` supports these values:

- `path` (string|null)
- `scheme` (string|null)
- `host` (string|null)
- `port` (int|null)
- `methods` (string[]|null)
- `middleware` (`array<Closure|MiddlewareInterface|string>`)
- `placeholders` (`array<string, string>` placeholder patterns)
- `as` (string|null)

```php
use Fyre\Router\Attributes\Route;

class WebhookController
{
    #[Route('webhook', methods: ['GET', 'POST'], as: 'webhook')]
    public function handle(): string
    {
        return '';
    }
}
```

### Hiding controllers and actions

Use `Fyre\Router\Attributes\Hidden` to prevent discovery:

- Place `#[Hidden]` on a controller class to skip all actions in that controller.
- Place `#[Hidden]` on a method to skip only that action.

## Conventions

When you don’t provide an explicit route `path`, `methods`, or `as`, the `RouteLocator` derives them from the controller name, method name, and method parameters.

### Default path

Path building follows this order:

1. If the method attribute provides a non-null `path` value, that `path` is used as-is.
2. Otherwise, a base path is chosen:
   - If the controller class attribute provides a `path` value, the path is split on `/` and used as the base segments.
   - Otherwise, segments are derived from the controller namespace folders and controller class name.
3. If the method name is not `index`, the method name is appended as an extra segment.
4. Each method parameter becomes a placeholder segment:
   - Required parameters → `{name}`
   - Optional parameters → `{name?}`

Naming rules:

- If the controller class name ends in `Controller` (and isn’t exactly `Controller`), the suffix is removed before generating segments.
- Controller and method names are “dasherized” (for example `doSomething` → `do-something`).
- Placeholder segments use the method parameter name as-is (for example `$postId` becomes `{postId}`).

If you use route bindings, keep placeholder names compatible with PHP parameter names (for example `{post}` or `{postId}`), since dashed placeholder names like `{post-id}` cannot be used as parameter names.

Example (pure conventions, no attributes):

```php
class PostsController
{
    public function show(string $post): string
    {
        return '';
    }
}
```

This produces a route with (paths are normalized to a leading `/` when connected):

- `path`: `posts/show/{post}`
- `methods`: `['GET']`
- `as`: `posts.show`

### Default methods

If no `methods` are provided via attributes, method names imply the HTTP method list:

- `create` → `['POST']`
- `delete` → `['DELETE']`
- `update` → `['PATCH', 'PUT']`
- everything else → `['GET']`

### Default alias

Alias generation follows this order:

1. If the method attribute provides an `as` value, it is used as-is.
2. Otherwise, if the controller class attribute provides an `as` value, the final alias is `"{classAs}.{methodNameDasherized}"`.
3. Otherwise, the alias is derived from the namespace folder segments, controller name, and method name, joined with `.` and dasherized (for example `admin/users/index` becomes `admin.users.index`).

### Controller defaults and method overrides

You can define defaults at the controller level and override at the method level:

- `scheme`, `host`, `port`, and `methods` use method-level values when provided; otherwise they fall back to controller-level defaults.
- `middleware` and `placeholders` are merged: controller values first, then method values.

If a method attribute provides a `path`, it replaces any controller `path` value (the controller `path` is not automatically prefixed or combined).

Example (class defaults + method override):

```php
use Fyre\Router\Attributes\Get;
use Fyre\Router\Attributes\Route;
use Fyre\Router\Middleware\SubstituteBindingsMiddleware;

#[Route('posts', as: 'posts', middleware: [SubstituteBindingsMiddleware::class])]
class PostsController
{
    #[Get]
    public function index(): string
    {
        return '';
    }

    #[Get('posts/{post}', as: 'posts.show', placeholders: ['post' => '\d+'])]
    public function show(string $post): string
    {
        return '';
    }
}
```

## Caching

Routes are cached per discovered namespace when a cache configuration named `_routes` exists in `Fyre\Cache\CacheManager`.

- Cache keys are built from the namespace by replacing `\` with `.` (for example `App\Http\Controllers` becomes `App.Http.Controllers`).
- Values are stored via `Fyre\Cache\Cacher::remember()`.
- `Fyre\Router\RouteLocator::clear()` clears discovered routes and calls `clear()` on the `_routes` cache handler.

If the cache manager is disabled, the `_routes` handler is a no-op cacher and routes are rebuilt on every discovery call.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Only the first attribute that is an instance of `Fyre\Router\Attributes\Route` is used on a controller class and on each method.
- `#[Hidden]` only takes effect if it is that first `Route`-instance attribute on the class/method.
- All public methods are route candidates, including inherited public methods.
- Discovered routes are sorted by most-specific path first (longer paths first).

## Related

- [Router](router.md)
- [Route Bindings](route-bindings.md)
- [URL Generation](url-generation.md)
- [Cache](../cache/index.md)
