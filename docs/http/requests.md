# HTTP Requests

`Fyre\Http\ServerRequest` represents an incoming HTTP request as a PSR-7 server request backed by PHP superglobals.

## Table of Contents

- [Purpose](#purpose)
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
  - [PSR-7 request basics](#psr-7-request-basics)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

`ServerRequest` represents what the client is asking for (method, target URI, headers, and body), plus server-provided context like query data, cookies, uploads, and server parameters.

Requests are immutable: any `with*` call returns a new instance, which makes it safe to enrich the request as it flows through middleware and handlers.

## Getting a server request

The most common way to work with `ServerRequest` is via dependency injection:

```php
use Psr\Http\Message\ServerRequestInterface;

function handle(ServerRequestInterface $request): string
{
    return $request->getMethod();
}
```

This page documents convenience methods on Fyre’s `ServerRequest` implementation, such as `getData()`, `getQuery()`, `isSecure()`, and `negotiate()`. If you type-hint `ServerRequestInterface`, only standard PSR-7 methods are available.

If helpers are loaded, the `request()` helper resolves the current request from the container (see [Helpers](../core/helpers.md)):

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

- `isSecure()` checks HTTPS indicators (including proxy headers)
- `isAjax()` checks `X-Requested-With: XMLHttpRequest`
- `isCli()` checks whether the runtime is `cli`

```php
$secure = $request->isSecure();
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

Most examples assume you already have a `$request` instance (via dependency injection). If helpers are loaded, you can also set `$request = request();` (see [Helpers](../core/helpers.md)).

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

$file = $request->getUploadedFile('avatar');

if ($file instanceof UploadedFile) {
    $file->moveTo('/path/to/avatar.jpg');
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

#### **Check HTTPS** (`isSecure()`)

Checks the `HTTPS` server parameter and common proxy headers (`X-Forwarded-Proto`, `Front-End-Https`).

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

### PSR-7 request basics

These are standard request operations available on `ServerRequest` through its PSR-7 interfaces.

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

A few behaviors are worth keeping in mind:

- `ServerRequest` lazily reads and caches values from PHP superglobals, so later changes to superglobals won’t be reflected.
- `getParsedBody()` always returns an array, but it can throw `RuntimeException` when JSON parsing fails for `application/json` requests.
- `getParsedBody()` treats `application/x-www-form-urlencoded` bodies specially only for `PUT`, `PATCH`, and `DELETE` requests; other cases fall back to `$_POST`.
- `withUploadedFiles()` expects `UploadedFile` instances (and nested arrays of them) and throws when other values are provided.
- `isSecure()` reflects what the server environment provides and checks proxy headers in addition to the `HTTPS` server parameter.
- `negotiate('content', $supported, strictMatch: true)` returns an empty string when no acceptable match is found.

## Related

- [HTTP Responses](responses.md)
- [HTTP Middleware](middleware.md)
- [Request Handler](request-handler.md)
- [Sessions](sessions.md)
- [URI](uri.md)
- [User Agents](user-agents.md)
- [Routing](../routing/index.md)
