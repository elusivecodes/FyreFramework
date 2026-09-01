# URL Generation

Use `Fyre\Router\Router::url()` or the `route()` helper to generate links from route aliases.

## Table of Contents

- [Start here](#start-here)
- [Generating a URL by alias](#generating-a-url-by-alias)
  - [Generate a URL (`Router::url()`)](#generate-a-url-routerurl)
- [Query strings and fragments](#query-strings-and-fragments)
- [Base URI and full URLs](#base-uri-and-full-urls)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use URL generation when you want stable links that don’t break when paths change:

- generate paths and full URLs from route aliases
- keep query strings and fragments out of hard-coded strings
- support subdirectory deployments via `App.baseUri`

## Generating a URL by alias

Routes become “named” by providing an alias when connecting them. Aliases are also affected by route groups (including alias prefixes). For placeholder and pattern rules, see [Path placeholders and patterns](router.md#path-placeholders-and-patterns).

```php
use Fyre\Router\Router;

$router = app(Router::class);
```

`route($name, $arguments, ...)` is the shorthand for `Router::url()`; see [Helpers](../core/helpers.md).

```php
$url = route('posts.show', ['id' => 42]);
```

### Generate a URL (`Router::url()`)

`$arguments` supplies values for `{placeholders}` in the route path.

Arguments:
- `$name` (`string`): the route alias.
- `$arguments` (`array`): placeholder values plus special keys (`?` and `#`).
- `$scheme` (`string|null`): override the scheme used for URL generation.
- `$host` (`string|null`): override the host used for URL generation.
- `$port` (`int|null`): override the port used for URL generation.
- `$full` (`bool|null`): whether to generate a full URL when a scheme and host are available.

```php
$url = $router->url('posts.show', [
    'id' => 42,
]);
```

If the placeholder uses a field override like `{post:slug}`, the argument key is still the placeholder name (`post`). The `:slug` portion is used when extracting a value from an ORM entity.

Required placeholders can be combined with static text and other placeholders in the same path segment:

```php
$router->get('files/{name}.{extension}', [FilesController::class, 'show'], as: 'files.show');

$url = $router->url('files.show', [
    'name' => 'report',
    'extension' => 'pdf',
]);
```

This generates `/files/report.pdf`.

Scheme, host, and port can be provided explicitly. Use `full: true` to generate an absolute URL when a scheme and host are available from the arguments, route, or `App.baseUri`.

```php
$url = $router->url(
    'account',
    scheme: 'https',
    host: 'example.com',
    full: true
);
```

## Query strings and fragments

`Router::url()` reserves two special argument keys:

- `?` for query parameters
- `#` for the fragment

```php
$url = $router->url('posts.show', [
    'id' => 42,
    '?' => ['page' => 2],
    '#' => 'comments',
]);
```

Query parameters are encoded using `Uri`’s query parameter helpers (see [URI](../http/uri.md)).

The `?` and `#` keys are handled separately and are not used for `{placeholder}` substitution.

## Base URI and full URLs

Set `App.baseUri` when your application runs from a subdirectory or when full URL generation needs a default scheme, host, and port.

The base URI affects two things:

- Request parsing: when `App.baseUri` contains a path (for example `/subdir`), that path is removed from the start of the incoming request path before route matching.
- URL generation: the base path is prepended back onto generated route paths, so links continue to work when the application is served from a subdirectory.

When generating full URLs, `App.baseUri` also acts as the default source for scheme, host, and port when they are not provided.

If the base URI path is empty or `/`, it has no effect on request parsing or URL generation. When it includes a non-root path, stripping during request parsing only occurs when the incoming request path starts with that base path.

To configure the base URI, set `App.baseUri` in your application config (see [Config](../core/config.md)). If you need to inspect it later, use `Router::getBaseUri()`.

## Behavior notes

- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when the alias does not exist.
- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when a required placeholder value is missing from `$arguments`.
- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when a placeholder value does not match the route’s pattern (or the default single-segment pattern).
- Placeholder values are normalized and cast to strings before validation.
- If a placeholder value is a `Fyre\ORM\Entity`, the router uses the model route key field; `{name:field}` uses `field` as an override when extracting the value from the entity.
- If a placeholder value is a PHP enum case, the router uses the backing value for backed enums and the case name for unit enums. The same normalization applies when an entity route field contains an enum case.
- Optional placeholders like `{id?}` use the base placeholder name for argument lookup (for example `['id' => 123]`).
- If a routed request is available, `Router::url()` returns a path-only URL when the scheme, host, and port match the current request, and a full URL when they differ.
- If no routed request is available, `Router::url()` defaults to full URL generation. An absolute result still requires both a scheme and host from the arguments, route, or `App.baseUri`.
- Port comparisons treat the scheme’s default port (for example 80/443) as equivalent to an omitted port on the current request.

## Related

- [Router](router.md)
- [Routing](index.md)
- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Route Bindings](route-bindings.md)
