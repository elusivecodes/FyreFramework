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
- [Method guide](#method-guide)
  - [Input data](#input-data)
  - [Uploaded files](#uploaded-files)
  - [Locale, negotiation, and user agent](#locale-negotiation-and-user-agent)
  - [Attributes](#attributes)
  - [Request context](#request-context)
  - [Common request basics](#common-request-basics)
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

`ServerRequest` includes convenience accessors for the most common server-side data sources:

- query parameters (`$_GET`)
- parsed body data (derived from `$_POST` and/or `php://input`, depending on content type and method)
- cookies (`$_COOKIE`)
- server parameters (`$_SERVER`)
- environment values (via `getenv()`)

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

Uploaded files are exposed as `UploadedFile` objects (from `$_FILES`) and can be retrieved using dot-notation keys. See [Method guide → Uploaded files](#uploaded-files) for examples.

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

## Request attributes

Attributes are request-scoped values that are not part of the HTTP message itself. They are commonly used by middleware to attach derived data (matched route, authenticated user, request IDs, and so on).

Attributes are typically written by middleware and read by downstream middleware/handlers. See [Method guide → Attributes](#attributes) for the `getAttribute()`/`withAttribute()` helpers.

## Method guide

This section focuses on the methods you’ll use most when working with `ServerRequest`.

Most examples assume you already have a `$request` instance (via dependency injection). You can also set `$request = request();` (see [Helpers](../core/helpers.md)).

### Input data

#### **Read query values** (`getQuery()`)

Reads from query parameters (`$_GET`) using dot-notation.

Arguments:
- `$key` (`string|null`): the key to read (use dot-notation). When `null`, returns the full query array.
- `$as` (`string|null`): optional type identifier (for example `int`, `bool`).

```php
$page = $request->getQuery('page', 'int') ?? 1;
```

#### **Read all query parameters** (`getQueryParams()`)

Returns the full query array (from `$_GET`).

```php
$query = $request->getQueryParams();
```

#### **Read body values** (`getData()`)

Reads from parsed body data using dot-notation.

Arguments:
- `$key` (`string|null`): the key to read (use dot-notation). When `null`, returns the full parsed body array.
- `$as` (`string|null`): optional type identifier (for example `int`, `bool`).

```php
$title = $request->getData('post.title');
```

Alternate helper syntax (shorthand for `$request->getData(...)`):

```php
$title = request('post.title');
$published = request('post.published', 'bool') ?? false;
```

#### **Read the parsed body array** (`getParsedBody()`)

Returns the parsed request body.

```php
$data = $request->getParsedBody();
```

#### **Read cookies** (`getCookie()`)

Reads from cookie parameters (`$_COOKIE`) using dot-notation.

Arguments:
- `$key` (`string|null`): the key to read (use dot-notation). When `null`, returns the full cookie array.
- `$as` (`string|null`): optional type identifier (for example `int`, `bool`).

```php
$session = $request->getCookie('session');
```

#### **Read server parameters** (`getServer()`)

Reads from server parameters (`$_SERVER`) using dot-notation.

Arguments:
- `$key` (`string|null`): the key to read (use dot-notation). When `null`, returns the full server array.
- `$as` (`string|null`): optional type identifier (for example `int`, `bool`).

```php
$method = $request->getServer('REQUEST_METHOD');
```

#### **Read environment variables** (`getEnv()`)

Reads values using `getenv()` (not `$_ENV`).

Arguments:
- `$key` (`string`): the environment variable key.
- `$as` (`string|null`): optional type identifier (for example `int`, `bool`).

```php
$debug = $request->getEnv('APP_DEBUG', 'bool') ?? false;
```

### Uploaded files

#### **Read uploaded files** (`getUploadedFile()`)

Returns an `UploadedFile` (or a nested array of uploads) from `$_FILES` using dot-notation.

Arguments:
- `$key` (`string|null`): the key to read (use dot-notation). When `null`, returns the full uploaded files structure.

```php
use Fyre\Http\UploadedFile;
use const UPLOAD_ERR_OK;

$file = $request->getUploadedFile('avatar');

if ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_OK) {
    $size = $file->getSize();
}
```

Notes:
- Always validate uploads (size, extension/MIME, and that the upload is present) before moving them.
- Avoid using the client-provided filename directly; generate a safe destination path/name instead.

### Locale, negotiation, and user agent

#### **Get the current locale** (`getLocale()`)

Returns the selected locale, falling back to `getDefaultLocale()` when no locale has been set.

```php
$locale = $request->getLocale();
```

#### **Override the locale** (`withLocale()`)

Returns a new request instance with the locale updated.

This method only accepts locales listed in `App.supportedLocales`, and will throw if the locale is not supported.

Arguments:
- `$locale` (`string`): the locale.

```php
$request = $request->withLocale('en');
```

#### **Negotiate a value from request headers** (`negotiate()`)

Negotiates a value from request headers for `content`, `encoding`, or `language`.

Arguments:
- `$type` (`'content'|'encoding'|'language'`): the negotiation type.
- `$supported` (`string[]`): supported values.
- `$strictMatch` (`bool`): whether to avoid a default fallback (applies to `content` negotiation).

```php
$language = $request->negotiate('language', ['en', 'en-US', 'fr']);
```

#### **Check whether the request prefers JSON** (`prefersJson()`)

Returns `true` when content negotiation prefers `application/json` over `text/html`.

```php
if ($request->prefersJson()) {
    // Return a JSON response.
}
```

#### **Read the parsed user agent** (`getUserAgent()`)

Returns the `UserAgent` built from the `User-Agent` header.

```php
$isRobot = $request->getUserAgent()->isRobot();
```

### Attributes

#### **Read an attribute** (`getAttribute()`)

Reads a request attribute value.

Arguments:
- `$key` (`string`): the attribute key.
- `$default` (`mixed`): the default value when not present.

```php
$id = $request->getAttribute('request_id');
```

#### **Write an attribute** (`withAttribute()`)

Returns a new request instance with an attribute added or replaced.

Arguments:
- `$key` (`string`): the attribute key.
- `$value` (`mixed`): the value to set.

```php
$request = $request->withAttribute('request_id', 'abc123');
```

#### **Remove an attribute** (`withoutAttribute()`)

Returns a new request instance without the given attribute key.

Arguments:
- `$key` (`string`): the attribute key.

```php
$request = $request->withoutAttribute('request_id');
```

### Request context

#### **Get the client IP** (`getClientIp()`)

Returns the client IP address for the request.

By default, this uses `REMOTE_ADDR`. When proxy trust is enabled with no trusted proxy list, it uses the validated rightmost value from `X-Forwarded-For`. When proxies are listed, it walks the header right-to-left and returns the first untrusted address. Resolution stops at malformed addresses.

```php
$ip = $request->getClientIp();
```

#### **Get trusted proxies** (`getTrustedProxies()`)

Returns the configured trusted proxy IPs.

```php
$trustedProxies = $request->getTrustedProxies();
```

#### **Check HTTPS** (`isSecure()`)

Checks the `HTTPS` server parameter and common proxy headers (`X-Forwarded-Proto`, `Front-End-Https`). When a trusted proxy list is configured, the immediate remote address must be present in it.

```php
$secure = $request->isSecure();
```

#### **Check AJAX** (`isAjax()`)

Checks for `X-Requested-With: xmlhttprequest`.

```php
$ajax = $request->isAjax();
```

#### **Check CLI runtime** (`isCli()`)

Checks whether the runtime is `cli`.

```php
$cli = $request->isCli();
```

### Common request basics

These are the standard request methods you will commonly use alongside the helpers above.

#### **Read the HTTP method** (`getMethod()`)

```php
$method = $request->getMethod();
```

#### **Read the request URI** (`getUri()`)

```php
$uri = $request->getUri();
```

#### **Read a header value** (`getHeaderLine()`)

```php
$accept = $request->getHeaderLine('Accept');
```

#### **Read the request body** (`getBody()`)

```php
$body = (string) $request->getBody();
```

## Behavior notes

A few practical details are worth keeping in mind:

- `getParsedBody()` always returns an array, but it throws `RuntimeException` when an `application/json` body is invalid or does not decode to an array.
- `getParsedBody()` treats `application/x-www-form-urlencoded` bodies specially only for `PUT`, `PATCH`, and `DELETE` requests; other cases fall back to `$_POST`.
- `withUploadedFiles()` expects `UploadedFileInterface` instances (and nested arrays of them) and throws when other values are provided.
- `getClientIp()` uses `REMOTE_ADDR` by default. Proxy trust with an empty trusted list accepts the rightmost forwarded address; a non-empty list restricts forwarding to explicitly trusted proxy hops.
- `negotiate('content', $supported, strictMatch: true)` returns an empty string when no acceptable match is found.

## Related

- [HTTP Responses](responses.md)
- [HTTP Middleware](middleware.md)
- [Request Handler](request-handler.md)
- [Sessions](sessions.md)
- [URI](uri.md)
- [User Agents](user-agents.md)
- [Routing](../routing/index.md)
