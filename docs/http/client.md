# HTTP Client

Use `Fyre\Http\Client` when your application needs to call external APIs, webhooks, or internal HTTP services.

It gives you convenient verb methods, an in-memory cookie jar, optional redirect following, and a configurable transport handler.

## Table of Contents

- [Start here](#start-here)
- [Making requests](#making-requests)
  - [Sending JSON](#sending-json)
  - [Sending prepared requests](#sending-prepared-requests)
- [Configuration](#configuration)
- [Redirects and cookies](#redirects-and-cookies)
  - [Redirect behavior](#redirect-behavior)
  - [Cookie behavior](#cookie-behavior)
- [Working with responses](#working-with-responses)
  - [Check status and read headers](#check-status-and-read-headers)
  - [Decode JSON responses](#decode-json-responses)
  - [Read response cookies](#read-response-cookies)
- [Handlers](#handlers)
  - [cURL handler](#curl-handler)
  - [Mock handler](#mock-handler)
  - [Custom handlers](#custom-handlers)
- [Testing](#testing)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. create a `Client`
2. make requests with `get()`, `post()`, and the other verb methods
3. inspect the returned `Client\Response`

## Making requests

Create a `Client`, then use the verb methods (`get()`, `post()`, …). Each verb method accepts:

- a URL (absolute, or relative when `baseUrl` is set)
- `$data` as either an array (query parameters for `GET`, or encoded body for other methods) or a string (query string for `GET`, raw body for other methods)
- optional per-request `$options` to override the client configuration

The available methods are `get()`, `post()`, `put()`, `patch()`, `delete()`, `head()`, `options()`, and `trace()`.

```php
use Fyre\Http\Client;

$client = new Client([
    'baseUrl' => 'https://api.example.com',
]);

$response = $client->get('/users', [
    'page' => 2,
]);

$users = $response->getJson();
```

Keep a single `Client` instance when you want cookies and default options to carry across multiple requests.

### Sending JSON

When you pass array `$data` to non-`GET` requests, the request body encoding depends on the request `Content-Type`. To send JSON, set a `Content-Type` header starting with `application/json`:

```php
$response = $client->post('/events', [
    'name' => 'signup',
], [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
]);

$payload = $response->getJson();
```

### Sending prepared requests

Use `send($request, $options = [])` when you already have a PSR-7 request but still want client redirects, cookies, mocks, and per-request options.

`sendRequest($request)` is the strict PSR-18 path: it delegates directly to the configured handler and bypasses those client conveniences.

You can seed the client's cookie jar with `addCookie($cookie)`. Use `getHandler()` when you need direct access to the configured transport.

## Configuration

`Client` accepts an options array at construction time, and each request method can provide an `$options` array that overrides the client defaults for that call.

Common options:

- `handler`: a handler instance, or a handler class name (defaults to `CurlHandler`)
- `baseUrl`: a base URL used to resolve relative request URLs
- `headers`: default request headers (merged/overridden by per-request headers)
- `auth`: `['type' => 'basic'|'digest', 'username' => string|null, 'password' => string|null]`
- `proxy`: `['username' => string|null, 'password' => string|null]` (used for a `Proxy-Authorization` header)
- `protocolVersion`: `'1.0'`, `'1.1'`, or `'2.0'`
- `timeout`: timeout in seconds (interpreted by handlers that support it, such as `CurlHandler`)
- `maxRedirects`: number of redirects to follow when using `Client::send()` (and the verb methods)
- `maxRedirectBodySize`: maximum number of bytes used to buffer a non-seekable request body for redirect replay (default: `16_777_216`)
- `sensitiveHeaders`: additional header names to remove from cross-origin redirects

Example:

```php
$client = new Client([
    'baseUrl' => 'https://api.example.com',
    'timeout' => 5,
    'maxRedirects' => 3,
    'auth' => [
        'type' => 'basic',
        'username' => 'api-user',
        'password' => 'api-pass',
    ],
]);
```

## Redirects and cookies

`Client::send()` and the verb methods can follow redirects when `maxRedirects` is greater than `0`.

### Redirect behavior

Relative `Location` values are resolved against the current request URI. Empty, malformed, non-HTTP(S), credential-bearing, and hostless redirect locations throw a `RequestException`. Redirect loops also throw a `RequestException`.

Redirect methods and bodies follow these rules:

- `303` changes every method except `HEAD` to `GET` and removes the request body
- `301` and `302` change `POST` to `GET` and remove the request body
- `307` and `308` preserve the method and body
- other preserved-method redirects rewind the request body before replaying it

When redirects are enabled, a non-seekable request body is copied to a seekable stream before the first request. If it cannot be read or exceeds `maxRedirectBodySize`, the request throws a `RequestException`.

Cross-origin redirects remove `Authorization`, `Proxy-Authorization`, and `Referer` headers, plus any names configured in `sensitiveHeaders`. Client `auth` and `ssl` options are not forwarded to the new origin.

### Cookie behavior

The client also keeps an in-memory cookie jar:

- cookies received from responses are stored on the `Client` instance
- cookies are matched by host-only or domain scope, path, expiry, and the `Secure` flag
- matching cookies are sent on later requests from that same client instance, with longer matching paths first
- cookies are not persisted beyond the life of the PHP process

Malformed cookies, invalid domains, `Secure` cookies received over HTTP, invalid `__Secure-` / `__Host-` cookies, and insecure cookies that would overwrite an overlapping secure cookie are ignored. The jar also limits individual cookie size, cookies per domain, total cookies, and the generated `Cookie` header size.

## Working with responses

`Client\Response` gives you the usual response information plus a few convenience helpers for client-side tasks.

### Check status and read headers

Use the status helper that matches the range you accept:

| Method | Matches |
| --- | --- |
| `isSuccess()` | `200` through `299` |
| `isOk()` | `200` through `399` |
| `isRedirect()` | `301`, `302`, `303`, `307`, or `308` |

You can also read the status and headers directly:

```php
$response = $client->get('https://api.example.com/status');

if (!$response->isOk()) {
    return;
}

$contentType = $response->getHeaderLine('Content-Type');
```

### Decode JSON responses

Use `getJson()` when the response body is JSON:

```php
$data = $response->getJson();
```

Notes:
- Valid JSON scalars are returned as their decoded PHP values (for example `true`, `123`, or `'ok'`), and a JSON `null` literal returns `null`.
- Invalid JSON throws a `RuntimeException`.

### Read response cookies

If the response includes `Set-Cookie` headers, `Response` can parse them into `Cookie` objects:

```php
$cookies = $response->getCookies();
$session = $response->getCookie('session');
```

## Handlers

The HTTP client delegates network I/O to a handler. Configure it through the `handler` option as either an instance or a class name.

```php
use Fyre\Http\Client;
use Fyre\Http\Client\Handlers\CurlHandler;

$client = new Client([
    'handler' => CurlHandler::class,
]);
```

### cURL handler

Implemented by `CurlHandler` (the default handler). It uses PHP’s cURL extension and supports a few handler-specific options:

- `timeout` (`int`): request timeout in seconds
- `ssl` (`array`): SSL options (`cert`, `password`, `key`)
- `verifyPeer` (`bool`): whether to verify the peer certificate

### Mock handler

Implemented by `MockHandler`. It returns pre-configured responses and performs no network I/O.

Mock matching supports:

- method + URL matching
- `*` wildcards in the mock URL
- an optional match callback to decide whether a mock applies to a request

### Custom handlers

To build a custom transport, extend `ClientHandler` and implement `send(RequestInterface $request, array $options = []): Response`.

## Testing

For PHPUnit tests, prefer the `HttpClientTestTrait` helpers documented in [HTTP Client Testing](../testing/http-client.md).

If you need to register mocks manually, `Client` provides a static mock facility via `Client::addMockResponse()` and `Client::clearMockResponses()`. While mocks are active, `Client::send()` uses them instead of the configured handler.

Mocks are global to the `Client` class (static), so ensure they’re cleared between tests (the trait does this automatically).

## Behavior notes

- When `auth.type` is set to `digest`, the client may make an initial `401` challenge request before retrying with credentials.
- When you pass array `$data` to non-`GET` requests, the request body encoding depends on the request `Content-Type`. If it does not start with `application/json`, the request is encoded as either `multipart/form-data` (when files/streams are present) or `application/x-www-form-urlencoded`, and `Content-Type` is set accordingly.
- Query parameters are merged recursively when building the final URI (including when the URL already contains a query string).

## Related

- [HTTP Client Testing](../testing/http-client.md)
- [Cookies](cookies.md)
- [URI](uri.md)
- [HTTP Responses](responses.md)
