# Route Discovery

Use `Fyre\Router\Router::discoverRoutes()` when you want controller attributes and conventions to define routes for you.

## Table of Contents

- [Start here](#start-here)
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

## Start here

Use route discovery when you want controller methods to become routes automatically:

- define routes with `#[Route]` and method attributes like `#[Get]` / `#[Post]`
- rely on conventions for paths, methods, and aliases when you don’t want to specify everything
- optionally cache discovered routes per namespace

Run discovery through `Router::discoverRoutes()`:

```php
$router->discoverRoutes([
    'Your\Controllers',
]);
```

For routing basics, see [Router](router.md). For URL generation, see [URL Generation](url-generation.md).

## Route attributes

Route discovery reads route attributes from controller classes and methods.

### HTTP method attributes

For the common case of “one method → one HTTP verb”, the router provides method-specific attributes:

- `Get`
- `Post`
- `Put`
- `Patch`
- `Delete`

All of these attributes live in `Fyre\Router\Attributes`.

These attributes can be applied to a controller class or a controller method. They support the same parameters as `Route`, except the HTTP method is fixed.

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

When you do not provide an explicit route `path`, `methods`, or `as`, discovery derives them from the controller name, method name, and method parameters.

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

If you configure a cache named `_routes`, discovered routes are cached per namespace. Otherwise, routes are rediscovered each time you call `discoverRoutes()`.

## Behavior notes

A few behaviors are worth keeping in mind:

- Use one route attribute per controller and per method.
- If you want discovery to skip a controller or action, make `#[Hidden]` the route attribute on that class or method.
- All public methods are route candidates, including inherited public methods.
- More specific discovered routes are connected before less specific ones.

## Related

- [Router](router.md)
- [Route Bindings](route-bindings.md)
- [URL Generation](url-generation.md)
- [Cache](../cache/index.md)
