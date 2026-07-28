# HTTP Client

Use `Fyre\Http\Client` when your application needs to call external APIs, webhooks, or internal HTTP services.

It gives you convenient verb methods, an in-memory cookie jar, optional redirect following, and a configurable transport handler.

## Table of Contents

- [Start here](#start-here)
- [Making requests](#making-requests)
  - [Sending JSON](#sending-json)
- [Configuration](#configuration)
- [Redirects and cookies](#redirects-and-cookies)
- [Working with responses](#working-with-responses)
  - [Check status and read headers](#check-status-and-read-headers)
  - [Decode JSON responses](#decode-json-responses)
  - [Read response cookies](#read-response-cookies)
- [Handlers](#handlers)
  - [cURL handler](#curl-handler)
  - [Mock handler](#mock-handler)
  - [Custom handlers](#custom-handlers)
- [Testing](#testing)
- [Method guide](#method-guide)
  - [`Client`](#client)
  - [`Response`](#response)
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

The client also keeps an in-memory cookie jar:

- cookies received from responses are stored on the `Client` instance
- matching cookies are sent on later requests from that same client instance
- cookies are not persisted beyond the life of the PHP process

## Working with responses

`Client\Response` gives you the usual response information plus a few convenience helpers for client-side tasks.

```php
use Fyre\Http\Client;

$client = new Client();
```

### Check status and read headers

Use `isOk()` when you want a simple “good enough to proceed” check (`200`-`399`), or read the status and headers directly:

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

## Method guide

If you are calling a service repeatedly, keep a single `Client` and reuse it so cookies and configuration persist.

### `Client`

#### **Make a GET request** (`get()`)

Performs a `GET` request. When `$data` is an array, it is treated as query parameters; when it is a string, it is treated as a query string.

Other verb methods follow the same argument conventions: `delete()`, `head()`, `options()`, `patch()`, `put()`, and `trace()`.

Arguments:
- `$url` (`string`): the request URL (absolute, or relative when `baseUrl` is set).
- `$data` (`array<string, mixed>|string`): query parameters (array) or a query string.
- `$options` (`array<string, mixed>`): per-request overrides (for example `headers`, `timeout`, `maxRedirects`).

```php
$response = $client->get('https://api.example.com/users', [
    'page' => 2,
]);
```

#### **Make a POST request** (`post()`)

Performs a `POST` request. When `$data` is an array, it is encoded into the request body.

Arguments:
- `$url` (`string`): the request URL.
- `$data` (`array<string, mixed>|string`): data to encode (array) or a raw body string.
- `$options` (`array<string, mixed>`): per-request overrides.

```php
$response = $client->post('https://api.example.com/events', [
    'name' => 'signup',
], [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
]);
```

#### **Send a request with client conveniences** (`send()`)

Sends a `RequestInterface` using the configured handler (or the mock handler when active). This is the method that applies redirect following (via `maxRedirects`) and the cookie jar.

Arguments:
- `$request` (`RequestInterface`): the request to send.
- `$options` (`array<string, mixed>`): per-request overrides (merged with the client defaults).

```php
use Fyre\Http\Client\Request;

$request = new Request('https://example.com', [
    'method' => 'GET',
]);

$response = $client->send($request, [
    'maxRedirects' => 3,
]);
```

#### **Send a request directly** (`sendRequest()`)

Implements `Psr\Http\Client\ClientInterface::sendRequest()` by delegating directly to the configured handler.

Arguments:
- `$request` (`RequestInterface`): the request to send.

```php
use Fyre\Http\Client\Request;

$request = new Request('https://example.com', [
    'method' => 'GET',
]);

$response = $client->sendRequest($request);
```

#### **Add a cookie to the cookie jar** (`addCookie()`)

Adds a `Cookie` to the client’s cookie jar.

Arguments:
- `$cookie` (`Cookie`): the cookie to add.

```php
use Fyre\Http\Cookie\Cookie;

$client->addCookie(new Cookie('token', 'abc123'));
```

### `Response`

`Client\Response` includes the usual response methods like `getStatusCode()`, `getHeaderLine()`, and `getBody()`, in addition to the helpers below.

```php
$response = $client->get('https://api.example.com/status');
```

#### **Read the status code** (`getStatusCode()`)

Returns the HTTP status code.

```php
$status = $response->getStatusCode();
```

#### **Read a header value** (`getHeaderLine()`)

Returns a header’s values as a single comma-separated string.

```php
$contentType = $response->getHeaderLine('Content-Type');
```

#### **Read the response body** (`getBody()`)

Returns the response body stream.

```php
$body = (string) $response->getBody();
```

#### **Read response cookies** (`getCookies()`)

Returns cookies parsed from the response `Set-Cookie` headers.

```php
$cookies = $response->getCookies();
```

#### **Read a response cookie** (`getCookie()`)

Returns the first cookie matching the provided cookie name.

Arguments:
- `$name` (`string`): the cookie name.

```php
$session = $response->getCookie('session');
```

#### **Decode JSON** (`getJson()`)

Decodes the response body as JSON and returns the decoded data.

```php
$data = $response->getJson();
```

#### **Check whether the response is OK** (`isOk()`)

Returns `true` for successful and redirect responses (`200`-`399`).

```php
if ($response->isOk()) {
    // ...
}
```

#### **Check whether the response is a redirect** (`isRedirect()`)

Returns `true` when the status code is a redirect and a non-empty `Location` header is present.

```php
if ($response->isRedirect()) {
    // ...
}
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `Client::sendRequest()` bypasses redirect following, cookie handling, mocks, and client options. Use `send()` or the verb methods for normal application code.
- When `auth.type` is set to `digest`, the client may make an initial `401` challenge request before retrying with credentials.
- When you pass array `$data` to non-`GET` requests, the request body encoding depends on the request `Content-Type`. If it does not start with `application/json`, the request is encoded as either `multipart/form-data` (when files/streams are present) or `application/x-www-form-urlencoded`, and `Content-Type` is set accordingly.
- Query parameters are merged recursively when building the final URI (including when the URL already contains a query string).

## Related

- [HTTP Client Testing](../testing/http-client.md)
- [URI](uri.md)
- [HTTP Responses](responses.md)
