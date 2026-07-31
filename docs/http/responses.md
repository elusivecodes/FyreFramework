# HTTP Responses

Use `ClientResponse` and its subclasses when you want to return HTML, JSON, redirects, downloads, headers, or cookies from your application.

All response objects are immutable, so every `with*` call returns a new instance.

## Table of Contents

- [Start here](#start-here)
- [Choosing a response type](#choosing-a-response-type)
- [Common response patterns](#common-response-patterns)
- [Redirect responses](#redirect-responses)
  - [Example: simple redirect](#example-simple-redirect)
  - [Status code behavior](#status-code-behavior)
- [Download responses](#download-responses)
  - [Example: download a file](#example-download-a-file)
  - [Example: download generated content](#example-download-generated-content)
  - [Header defaults](#header-defaults)
- [Emitting responses](#emitting-responses)
- [Method guide](#method-guide)
  - [`ClientResponse`](#clientresponse)
  - [`RedirectResponse`](#redirectresponse)
  - [`DownloadResponse`](#downloadresponse)
  - [`ResponseEmitter`](#responseemitter)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In most application code:

- use `response()` when you want a general response object
- use `json($data)` when you want a JSON response quickly
- use `RedirectResponse` or `redirect()` for redirects
- use `DownloadResponse` for file or generated-content downloads

```php
$response = response()
    ->withContentType('text/plain')
    ->withHeader('X-Request-Id', 'abc123');
```

## Choosing a response type

Pick the response type that matches what you’re trying to send:

- `ClientResponse`: general “send something to a client” responses (HTML/text/JSON/XML, cache headers, cookies).
- `Response`: lower-level response when you do not need the client-focused helpers.
- `RedirectResponse`: sets `Location` and a redirect status code (subclass of `ClientResponse`).
- `DownloadResponse`: sets a stream body and common download headers (subclass of `ClientResponse`).

## Common response patterns

`ClientResponse` is the usual choice for server responses. It gives you a sensible default content type, plus helpers for JSON, XML, cookies, cache headers, and dates.

### Example: build a response

```php
use Fyre\Http\ClientResponse;

$response = new ClientResponse([
    'statusCode' => 200,
    'body' => 'Hello',
]);
```

### Example: return JSON

```php
use Fyre\Http\ClientResponse;

$response = new ClientResponse()
    ->withJson(['ok' => true]);
```

### Example: set and expire cookies

```php
use Fyre\Http\ClientResponse;

$response = new ClientResponse()
    ->withCookie('session', 'abc123', httpOnly: true, secure: true)
    ->withExpiredCookie('legacy_session');
```

`response()` resolves a `ClientResponse` from the container, and `json($data)` is shorthand for `response()->withJson($data)` (see [Helpers](../core/helpers.md)).

## Redirect responses

`RedirectResponse` sets the `Location` header and a redirect status code. It’s a `ClientResponse`, so it can also carry cookies and other headers.

### Example: simple redirect

```php
use Fyre\Http\RedirectResponse;

$response = new RedirectResponse('/login');
```

`redirect($uri, $code, $options)` resolves a `RedirectResponse` via the container (see [Helpers](../core/helpers.md)).

### Status code behavior

When `$_SERVER['REQUEST_METHOD']` is available and the response protocol version is `>= 1.1`, the constructor may adjust the status code:

- Non-`GET` requests force `303 See Other`.
- `GET` requests convert the default `302` to `307 Temporary Redirect` (other codes are left as-is).

For non-`GET` requests, the status code is always changed to `303` when the request method is available.

To avoid this adjustment for `GET` requests, use a redirect code other than `302` or set `protocolVersion` to `1.0`.

## Download responses

`DownloadResponse` builds a response suitable for downloads by setting the body to a stream and populating common headers (such as `Content-Disposition` and `Content-Length`). It’s a `ClientResponse`, so it can also carry cookies and other headers.

### Example: download a file

```php
use Fyre\Http\DownloadResponse;

$response = DownloadResponse::createFromFile(
    '/path/to/report.pdf',
    'report.pdf'
);
```

### Example: download generated content

```php
use Fyre\Http\DownloadResponse;

$response = DownloadResponse::createFromString(
    'Example export content',
    'export.txt',
    'text/plain'
);
```

### Header defaults

Both download builders set the usual download headers for you, including `Content-Type`, `Content-Disposition`, and `Content-Length`.

## Emitting responses

`ResponseEmitter` sends a response to the client by outputting the status code, headers, cookies, and body stream.

### Example: emit a response

```php
use Fyre\Http\ClientResponse;
use Fyre\Http\ResponseEmitter;

$response = new ClientResponse(['body' => 'Hello'])
    ->withContentType('text/plain')
    ->withCookie('session', 'abc123', httpOnly: true, secure: true);

$emitter = new ResponseEmitter();
$emitter->emit($response);
```

Most applications do not create the emitter directly because the framework handles response emission for you, but it is useful in custom entry points.

## Method guide

This section focuses on the most-used response methods, grouped by class.

Most examples assume you already have a `$response` instance (via dependency injection). You can also set `$response = response();` (see [Helpers](../core/helpers.md)). Examples commonly reassign `$response` to emphasize immutability.

Examples below assume relevant classes are already imported when needed.

### `ClientResponse`

#### **Set the content type** (`withContentType()`)

Sets the `Content-Type` header with a MIME type and optional charset.

Arguments:
- `$mimeType` (`string`): the MIME type (for example `application/json`).
- `$charset` (`string`): the charset (defaults to `UTF-8`).

```php
$response = $response->withContentType('text/plain');
```

#### **Write a JSON body** (`withJson()`)

Sets `Content-Type` to `application/json` and writes a pretty-printed JSON body. A `JsonException` is thrown if the data cannot be encoded.

Arguments:
- `$data` (`mixed`): the data to encode.

```php
$response = $response->withJson([
    'ok' => true,
]);
```

#### **Write an XML body** (`withXml()`)

Sets `Content-Type` to `application/xml` and writes the XML body.

Arguments:
- `$data` (`SimpleXMLElement`): the XML document.

```php
$xml = new SimpleXMLElement('<root/>');
$xml->addChild('ok', '1');

$response = $response->withXml($xml);
```

#### **Set a header (replace existing values)** (`withHeader()`)

Sets a header and replaces any existing values for that header.

Arguments:
- `$name` (`string`): the header name.
- `$value` (`mixed`): a string/number value, or an array of values.

```php
$response = $response->withHeader('X-Request-Id', 'abc123');
```

#### **Add header values** (`withAddedHeader()`)

Adds values to a header. If the header does not exist, it is created.

Arguments:
- `$name` (`string`): the header name.
- `$value` (`mixed`): a string/number value, or an array of values.

```php
$response = $response
    ->withAddedHeader('Cache-Control', 'no-store')
    ->withAddedHeader('Cache-Control', 'max-age=0');
```

#### **Remove a header** (`withoutHeader()`)

Removes a header entirely.

Arguments:
- `$name` (`string`): the header name.

```php
$response = $response
    ->withHeader('X-Debug', '1')
    ->withoutHeader('X-Debug');
```

#### **Add a cookie** (`withCookie()`)

Adds a cookie to the response cookie collection. These cookies are emitted later by `ResponseEmitter` (they are not written to a `Set-Cookie` header by this method).

Arguments:
- `$name` (`string`): the cookie name.
- `$value` (`string`): the cookie value.
- `$expires` (`Fyre\Utility\DateTime\DateTime|int|null`): expiration time (`DateTime` or UNIX timestamp).
- `$path` (`string`): cookie path (defaults to `/`).
- `$domain` (`string`): cookie domain.
- `$httpOnly` (`bool`): whether the cookie is HTTP only.
- `$secure` (`bool`): whether the cookie is secure.
- `$sameSite` (`string`): same-site mode (`lax`, `strict`, `none`).

```php
$response = $response
    ->withCookie('session', 'abc123', httpOnly: true, secure: true);
```

#### **Expire a cookie** (`withExpiredCookie()`)

Convenience wrapper that adds an expired cookie for the given name.

Arguments:
- `$name` (`string`): the cookie name.
- `$path` (`string`): cookie path (defaults to `/`).
- `$domain` (`string`): cookie domain.
- `$httpOnly` (`bool`): whether the cookie is HTTP only.
- `$secure` (`bool`): whether the cookie is secure.
- `$sameSite` (`string`): same-site mode (`lax`, `strict`, `none`).

```php
$response = $response->withExpiredCookie('session');
```

#### **Disable caching** (`withDisabledCache()`)

Sets `Cache-Control` to `no-store`, `max-age=0`, and `no-cache`.

Arguments: none.

```php
$response = $response->withDisabledCache();
```

#### **Set the Date header** (`withDate()`)

Sets the `Date` header (formatted in UTC).

Arguments:
- `$date` (`Fyre\Utility\DateTime\DateTime|DateTimeInterface|int|string`): a date value (timestamps and parseable strings are supported).

```php
$response = $response->withDate('2026-01-31 12:00:00');
```

#### **Set the Last-Modified header** (`withLastModified()`)

Sets the `Last-Modified` header (formatted in UTC).

Arguments:
- `$date` (`Fyre\Utility\DateTime\DateTime|DateTimeInterface|int|string`): a date value (timestamps and parseable strings are supported).

```php
$response = $response->withLastModified(1700000000);
```

#### **Set status and reason phrase** (`withStatus()`)

Sets the HTTP status code and optional reason phrase. If you pass an empty reason phrase (or omit it), a default phrase is used when available for the status code.

Arguments:
- `$code` (`int`): the status code (must be `100`–`599`).
- `$reasonPhrase` (`string`): optional reason phrase.

```php
$response = $response->withStatus(404);
```

### `RedirectResponse`

#### **Create a redirect response** (`__construct()`)

Sets the `Location` header and a redirect status code.

Arguments:
- `$uri` (`string|Psr\Http\Message\UriInterface`): the URI to redirect to.
- `$code` (`int`): the initial status code (defaults to `302`).
- `$options` (`array`): response options (headers, protocol version, and so on).

```php
$response = new RedirectResponse('/login', 302);
```

### `DownloadResponse`

#### **Create a download from a file** (`createFromFile()`)

Creates a response with a file-backed stream body and common download headers.

Arguments:
- `$path` (`string`): the file path.
- `$filename` (`string|null`): the download filename (defaults to the file basename).
- `$mimeType` (`string|null`): the MIME type (auto-detected when omitted).
- `$options` (`array`): response options (headers, protocol version, and so on).

```php
$response = DownloadResponse::createFromFile('/path/to/report.pdf');
```

#### **Create a download from a string** (`createFromString()`)

Creates a response with an in-memory stream body and common download headers.

Arguments:
- `$content` (`string`): the content to send.
- `$filename` (`string`): the download filename.
- `$mimeType` (`string|null`): the MIME type (auto-detected when omitted).
- `$options` (`array`): response options (headers, protocol version, and so on).

```php
$response = DownloadResponse::createFromString('Example export content', 'export.txt');
```

### `ResponseEmitter`

#### **Emit a response** (`emit()`)

Sends a `Psr\Http\Message\ResponseInterface` to the client using PHP’s `header()` / `http_response_code()` and streams the body.

Arguments:
- `$response` (`Psr\Http\Message\ResponseInterface`): the response to send.

```php
$emitter = new ResponseEmitter();
$emitter->emit($response);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Response objects are immutable, so remember to keep the value returned by each `with*` call.
- `ClientResponse::withCookie()` stores cookies in a response cookie collection, and `ResponseEmitter` emits them when sending the response.
- When the request method is available and the protocol version is `>= 1.1`, non-`GET` redirects force `303`, and `GET` redirects convert a default `302` to `307`.

## Related

- [HTTP Requests](requests.md)
- [Cookies](cookies.md)
- [HTTP Middleware](middleware.md)
- [Request Handler](request-handler.md)
- [Sessions](sessions.md)
- [Helpers](../core/helpers.md)
- [Content Security Policy (CSP)](../security/csp.md)
