# HTTP Requests

Use `Fyre\Http\ServerRequest` to read incoming request data, headers, uploaded files, locale preferences, and request-scoped attributes.

## Table of Contents

- [Start here](#start-here)
- [Getting a server request](#getting-a-server-request)
- [Reading request input](#reading-request-input)
- [Working with uploaded files](#working-with-uploaded-files)
- [Inspecting request context](#inspecting-request-context)
- [Locale and negotiation](#locale-and-negotiation)
- [Request attributes](#request-attributes)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In most request-handling code:

- use `getData()` for parsed body values and `getQuery()` for query string values
- use `getUploadedFile()` for uploads and `getAttribute()` for middleware-added context
- use `prefersJson()` or `negotiate()` when the response depends on the request headers
- use `request()` when you need the current request from the container

Requests are immutable, so any `with*` call returns a new instance.

## Getting a server request

The most common way to work with `ServerRequest` is via dependency injection:

```php
use Psr\Http\Message\ServerRequestInterface;

function handle(ServerRequestInterface $request): string
{
    return $request->getMethod();
}
```

This page focuses on the convenience methods Fyre adds on top of the standard request API, such as `getData()`, `getQuery()`, `getClientIp()`, `isSecure()`, `prefersJson()`, and `negotiate()`. If you type-hint `ServerRequestInterface`, you only get the standard request methods.

The `request()` helper resolves the current request from the container (see [Helpers](../core/helpers.md)):

```php
$request = request();
```

`request()` returns the request object when called with no arguments. When called as `request($key, $as)`, it is shorthand for reading parsed body data, equivalent to `$request->getData($key, $as)` when `$request = request()`.

## Reading request input

`ServerRequest` includes convenience accessors for the most common server-side request data:

| Source | Read a value | Read all values |
| --- | --- | --- |
| query parameters | `getQuery($key, $as = null)` | `getQueryParams()` |
| parsed body | `getData($key, $as = null)` | `getParsedBody()` |
| cookies | `getCookie($key, $as = null)` | `getCookieParams()` |
| server parameters | `getServer($key, $as = null)` | `getServerParams()` |
| environment | `getEnv($key, $as = null)` | — |

`getEnv()` reads through `getenv()` rather than `$_ENV`.

The current request resolved by the `Engine` is populated by `ServerRequestFactory::createFromGlobals()`, which imports the corresponding PHP superglobals and uses `php://input` as the request body. `ServerRequest` itself does not read those superglobals or derive its method, headers, URI, or uploaded files from server parameters. See [HTTP Factories](factories.md) for synthetic and non-global request creation.

Most accessors support:

- dot-notation (`post.title`, `user.profile.id`) for nested arrays
- an optional `$as` type identifier (for example `int` or `bool`) to parse values

```php
$title = $request->getData('post.title');
$published = $request->getData('post.published', 'bool') ?? false;

$page = $request->getQuery('page', 'int') ?? 1;
$session = $request->getCookie('session');
```

## Working with uploaded files

Uploaded files are exposed as `UploadedFile` objects and can be retrieved using dot-notation keys. For the current PHP request, `ServerRequestFactory::createFromGlobals()` normalizes `$_FILES` into these objects.

```php
use Fyre\Http\UploadedFile;
use const UPLOAD_ERR_OK;

$file = $request->getUploadedFile('profile.avatar');

if ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_OK) {
    $size = $file->getSize();
}
```

Validate an upload's presence, size, and type before moving it. Generate a safe destination name instead of using the client-provided filename directly.

## Inspecting request context

For common environment-derived checks:

- `getClientIp()` returns `REMOTE_ADDR` by default and can use `X-Forwarded-For` when proxy trust is enabled
- `isSecure()` checks native HTTPS indicators and trusted proxy headers
- `isAjax()` checks `X-Requested-With: XMLHttpRequest`
- `isCli()` checks whether the runtime is `cli`

```php
$clientIp = $request->getClientIp();
$secure = $request->isSecure();
```

If the application runs behind a trusted reverse proxy, enable proxy trust and list the proxies that may supply forwarded headers in the application config:

```php
return [
    'App' => [
        'trustProxy' => true,
        'trustedProxies' => ['127.0.0.1'],
    ],
];
```

`getTrustedProxies()` returns the configured list. With proxy trust enabled, `getClientIp()` walks `X-Forwarded-For` from right to left and returns the first untrusted valid address. `isSecure()` accepts forwarded HTTPS indicators only from a trusted proxy when a list is configured.

## Locale and negotiation

`ServerRequest` supports language/encoding/content negotiation based on standard HTTP headers.

If `App.supportedLocales` is configured (see [Config](../core/config.md)) and the request includes `Accept-Language`, a locale can be negotiated at construction time. The current locale falls back to the default locale when no specific locale has been selected.

```php
$contentType = $request->negotiate('content', [
    'application/json',
    'text/html',
]);

$prefersJson = $contentType === 'application/json';
```

If you just need a boolean check for the common HTML-vs-JSON case, use `prefersJson()`:

```php
$prefersJson = $request->prefersJson();
```

Negotiation falls back to the first value in your supported list when there is no match (or no header). For `content` negotiation, pass `strictMatch: true` to return an empty string instead of falling back:

```php
$contentType = $request->negotiate('content', [
    'application/json',
    'text/html',
], strictMatch: true);

if ($contentType === '') {
    // No acceptable content type matched.
}
```

`getLocale()` returns the selected locale. `withLocale($locale)` returns a new request and rejects locales that are not listed in `App.supportedLocales`. `getUserAgent()` returns the parsed `UserAgent` for the request's `User-Agent` header.

## Request attributes

Attributes are request-scoped values that are not part of the HTTP message itself. They are commonly used by middleware to attach derived data (matched route, authenticated user, request IDs, and so on).

Attributes are typically written by middleware and read by downstream middleware or handlers.

- `getAttribute($key, $default = null)` reads a value.
- `withAttribute($key, $value)` returns a request with the value added or replaced.
- `withoutAttribute($key)` returns a request without that value.

Router middleware stores matched placeholder values in the `routeArguments` attribute. Binding middleware replaces those values as each argument is resolved, so downstream middleware and handlers receive the bound values. See [Route Bindings](../routing/route-bindings.md).

## Behavior notes

- `getParsedBody()` always returns an array, but it throws `BadRequestException` when an `application/json` body is invalid or does not decode to an array.
- `getParsedBody()` parses `application/x-www-form-urlencoded` bodies only for `PUT`, `PATCH`, and `DELETE` requests. Otherwise, it uses parsed body data supplied when the request was created or an empty array. `ServerRequestFactory::createFromGlobals()` supplies `$_POST` for the current PHP request when it is not empty.
- `withUploadedFiles()` expects `UploadedFileInterface` instances (and nested arrays of them) and throws when other values are provided.
- `getClientIp()` uses `REMOTE_ADDR` by default. Proxy trust with an empty trusted list accepts the rightmost forwarded address; a non-empty list restricts forwarding to explicitly trusted proxy hops.
- `negotiate('content', $supported, strictMatch: true)` returns an empty string when no acceptable match is found.

## Related

- [HTTP Responses](responses.md)
- [HTTP Middleware](middleware.md)
- [Request Handler](request-handler.md)
- [HTTP Factories](factories.md)
- [Sessions](sessions.md)
- [URI](uri.md)
- [User Agents](user-agents.md)
- [Routing](../routing/index.md)
