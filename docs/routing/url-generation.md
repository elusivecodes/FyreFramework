# URL Generation

`Fyre\Router\Router::url()` generates URLs from route aliases, substituting placeholder values and applying base URI behavior.

## Table of Contents

- [Purpose](#purpose)
- [How URL generation works](#how-url-generation-works)
- [Generating a URL by alias](#generating-a-url-by-alias)
  - [Generate a URL (`Router::url()`)](#generate-a-url-routerurl)
- [Query strings and fragments](#query-strings-and-fragments)
- [Base URI and full URLs](#base-uri-and-full-urls)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use URL generation when you want stable links that don’t break when paths change:

- generate paths and full URLs from route aliases
- keep query strings and fragments out of hard-coded strings
- support subdirectory deployments via `App.baseUri`

## How URL generation works

URL generation is driven by route aliases (`as`). Instead of hard-coding `'/posts/42'`, generate a URL from an alias and a set of placeholder values.

`Router::url()` looks up the route by alias, substitutes `{placeholders}` using `$arguments`, and then decides whether to return a path-only URL (like `/posts/42`) or a full URL (like `https://example.com/posts/42`).

When a request has been routed via `Router::parseRequest()`, the router stores the matched request internally and uses it as the default comparison context for full URL decisions (scheme/host/port).

## Generating a URL by alias

Routes become “named” by providing an alias when connecting them. Aliases are also affected by route groups (including alias prefixes). For placeholder and pattern rules, see [Path placeholders and patterns](router.md#path-placeholders-and-patterns).

```php
use Fyre\Router\Router;

$router = app(Router::class);
```

If helpers are loaded, `route($name, $arguments, ...)` is the shorthand for `Router::url()`; see [Helpers](../core/helpers.md).

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
- `$full` (`bool|null`): whether to force a full (absolute) URL.

```php
$url = $router->url('posts.show', [
    'id' => 42,
]);
```

If the placeholder uses a field override like `{post:slug}`, the argument key is still the placeholder name (`post`). The `:slug` portion is used when extracting a value from an ORM entity.

Scheme/host/port can be provided explicitly. Use `full: true` to force an absolute URL.

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

The router reads `App.baseUri` during construction and stores it as a `Uri` (see [URI](../http/uri.md)).

The base URI affects two things:

- Request parsing: when `App.baseUri` contains a path (for example `/subdir`), that path is removed from the start of the incoming request path before route matching.
- URL generation: the base path is prepended back onto generated route paths, so links continue to work when the application is served from a subdirectory.

When generating full URLs (`full: true`, or when `Router::url()` decides a full URL is required), `App.baseUri` also acts as the default source for scheme/host/port when they are not provided.

If the base URI path is empty or `/`, it has no effect on request parsing or URL generation. When it includes a non-root path, stripping during request parsing only occurs when the incoming request path starts with that base path.

To configure the base URI, set `App.baseUri` in your application config (see [Config](../core/config.md)). You can also read the router’s configured base URI at runtime via `Router::getBaseUri()`.

## Behavior notes

A few behaviors are worth keeping in mind:

- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when the alias does not exist.
- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when a required placeholder value is missing from `$arguments`.
- `Router::url()` throws `Fyre\Router\Exceptions\RouterException` when a placeholder value does not match the route’s pattern (or the default single-segment pattern).
- Placeholder values are cast to strings before validation.
- If a placeholder value is a `Fyre\ORM\Entity`, the router uses the model route key field; `{name:field}` uses `field` as an override when extracting the value from the entity.
- Optional placeholders like `{id?}` use the base placeholder name for argument lookup (for example `['id' => 123]`).
- If a routed request is available, `Router::url()` defaults to returning a path-only URL when the scheme/host/port match the current request, and a full URL when they differ.
- If no routed request is available, `Router::url()` defaults to returning a full URL unless `full: false` is provided.
- Port comparisons treat the scheme’s default port (for example 80/443) as equivalent to an omitted port on the current request.

## Related

- [Router](router.md)
- [Routing](index.md)
- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Route Bindings](route-bindings.md)
