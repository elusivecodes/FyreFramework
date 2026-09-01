# HTTP Responses

Use `ClientResponse` and its subclasses when you want to return HTML, JSON, redirects, downloads, headers, or cookies from your application.

All response objects are immutable, so every `with*` call returns a new instance.

## Table of Contents

- [Start here](#start-here)
- [Choosing a response type](#choosing-a-response-type)
- [Common response patterns](#common-response-patterns)
- [Client response helpers](#client-response-helpers)
- [Streaming JSON responses](#streaming-json-responses)
- [Redirect responses](#redirect-responses)
  - [Example: simple redirect](#example-simple-redirect)
  - [Status code behavior](#status-code-behavior)
- [Download responses](#download-responses)
  - [Example: download a file](#example-download-a-file)
  - [Example: download generated content](#example-download-generated-content)
  - [Header defaults](#header-defaults)
- [Emitting responses](#emitting-responses)
- [Related](#related)

## Start here

In most application code:

- use `response()` when you want a general response object
- use `json($data)` when you want a JSON response quickly
- use `json($data, stream: true)` when a JSON array is too large to buffer in memory
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

`response()` resolves a `ClientResponse` from the container, and `json($data)` is shorthand for `response()->withJson($data)` (see [Helpers](../core/helpers.md)):

```php
return json(['ok' => true]);
```

Cookies can be added and expired on the same immutable response:

```php
$response = response()
    ->withCookie('session', 'abc123', httpOnly: true, secure: true)
    ->withExpiredCookie('legacy_session');
```

Cookies added with `withCookie()` and `withExpiredCookie()` are stored separately from the response headers. `ResponseEmitter` writes them when the response is sent.

## Client response helpers

`ClientResponse` inherits the standard PSR-7 methods for status codes, headers, protocol version, and body streams. These additional helpers cover common server responses:

| Method | Result |
| --- | --- |
| `withContentType($mimeType, $charset = 'UTF-8')` | set the `Content-Type` header |
| `withJson($data, $stream = false)` | set an `application/json` body |
| `withXml($data)` | set an `application/xml` body from a `SimpleXMLElement` |
| `withCookie($name, $value, ...)` | add a response cookie |
| `withExpiredCookie($name, ...)` | add an expired response cookie |
| `getCookie($name)` / `getCookies()` | read response cookies |
| `hasCookie($name)` | check for a response cookie by name |
| `withDisabledCache()` | set `Cache-Control: no-store, max-age=0, no-cache` |
| `withDate($date)` | set a UTC `Date` header |
| `withLastModified($date)` | set a UTC `Last-Modified` header |

Date helpers accept a framework `DateTime`, a `DateTimeInterface`, a Unix timestamp, or a parseable string.

## Streaming JSON responses

Streaming JSON responses incrementally encode an iterable as a JSON array. They are useful with unbuffered database or ORM results because the complete response is not held in memory.

```php
$users = $Users->find()
    ->disableBuffering()
    ->all();

$response = response()->withJson($users, stream: true);
```

The `json()` helper supports the same option:

```php
return json($users, stream: true);
```

The iterable values, including objects, become the JSON array items; iterable keys are ignored. Streamed JSON bodies are read-only and non-seekable, and the response does not set `Content-Length`.

If encoding fails after output has started, the client may receive incomplete JSON. Validate uncertain values first or use `ClientResponse::withJson()` without streaming when atomic encoding is required.

## Redirect responses

`RedirectResponse` sets the `Location` header and a redirect status code. It’s a `ClientResponse`, so it can also carry cookies and other headers.

### Example: simple redirect

```php
use Fyre\Http\RedirectResponse;

return new RedirectResponse('/login');
```

`redirect($uri, $code, $options)` resolves a `RedirectResponse` via the container (see [Helpers](../core/helpers.md)).

### Status code behavior

When `$_SERVER['REQUEST_METHOD']` is available and the response protocol version is `>= 1.1`, the constructor may adjust the status code:

- Non-`GET` requests force `303 See Other`.
- `GET` requests convert the default `302` to `307 Temporary Redirect` (other codes are left as-is).

For non-`GET` requests, the status code is always changed to `303` when the request method is available.

To avoid this adjustment for `GET` requests, use a redirect code other than `302` or set `protocolVersion` to `1.0`.

## Download responses

`DownloadResponse` builds a response suitable for downloads by setting the body to a stream and adding download headers. It is a `ClientResponse`, so it can also carry cookies and other headers.

### Example: download a file

```php
use Fyre\Http\DownloadResponse;

return DownloadResponse::createFromFile(
    '/path/to/report.pdf',
    'report.pdf'
);
```

### Example: download generated content

```php
use Fyre\Http\DownloadResponse;

return DownloadResponse::createFromString(
    'Example export content',
    'export.txt',
    'text/plain'
);
```

### Header defaults

Both builders preserve explicitly supplied headers and otherwise set these defaults:

| Header | Default |
| --- | --- |
| `Content-Type` | detected or supplied MIME type with `charset=UTF-8` |
| `Content-Disposition` | `attachment` with the download filename |
| `Content-Length` | file size or string length in bytes |
| `Content-Transfer-Encoding` | `binary` |
| `Expires` | `0` |
| `Cache-Control` | `private, no-transform, no-store, must-revalidate` |

`createFromFile()` rejects missing files and detects the MIME type when it is omitted. `createFromString()` stores the generated content in a `php://temp` stream.

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

The application front controller normally resolves the emitter and calls it after request handling. Create one directly only for a custom entry point (see [Getting Started](../getting-started.md)).

Pass the current request as the optional second argument when emitting manually. Bodies are suppressed for `HEAD` requests, informational responses, `204`, and `304`.

When a response contains a valid `Content-Range` header, the emitter outputs only that byte range. Seekable streams are read in chunks; a non-seekable stream must be read into memory for range handling.

## Related

- [HTTP Requests](requests.md)
- [Cookies](cookies.md)
- [HTTP Middleware](middleware.md)
- [Request Handler](request-handler.md)
- [Sessions](sessions.md)
- [Helpers](../core/helpers.md)
- [Content Security Policy (CSP)](../security/csp.md)
