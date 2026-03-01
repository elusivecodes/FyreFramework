# HTTP Client Testing

`HttpClientTestTrait` provides PHPUnit helpers for mocking `Fyre\Http\Client` responses in tests and clearing those mocks after each test.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [How it works](#how-it-works)
- [Method guide](#method-guide)
  - [`HttpClientTestTrait`](#httpclienttesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `HttpClientTestTrait` when you want tests that exercise code using `Client` without performing real network I/O.

## Quick start

Mock a `GET` request and assert that your code received the expected response:

```php
use Fyre\Http\Client;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\HttpClientTestTrait;

final class ApiClientTest extends TestCase
{
    use HttpClientTestTrait;

    public function testFetchesUser(): void
    {
        $response = $this->createResponse(
            200,
            ['Content-Type' => 'application/json'],
            '{"id":1,"name":"Ada"}'
        );

        $this->mockClientGet('https://api.example.com/users/1', $response);

        $client = new Client();
        $result = $client->get('https://api.example.com/users/1');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(['id' => 1, 'name' => 'Ada'], $result->getJson());
    }
}
```

## How it works

🧠 `HttpClientTestTrait` registers mock responses on `Client` and clears them automatically after each test.

- Registers mock responses via `Client::addMockResponse()` (method + URL).
- Optionally filters matches with a callback (`Closure(RequestInterface): bool`).
- Clears all mocks after each test using PHPUnit’s `#[After]` hook.

## Method guide

Most examples assume you’re in a `TestCase` using `HttpClientTestTrait`, and you already have a `$client = new Client()`.

### `HttpClientTestTrait`

#### **Create a response** (`createResponse()`)

Create a `Fyre\Http\Client\Response` instance for use with the mock helpers.

Arguments:
- `$statusCode` (`int`): the HTTP status code (default: `200`).
- `$headers` (`array<string, string|string[]>`): response headers (default: `[]`).
- `$body` (`string`): response body (default: `''`).

```php
$response = $this->createResponse(204, ['X-Test' => '1']);

$this->assertSame(204, $response->getStatusCode());
$this->assertSame('1', $response->getHeaderLine('X-Test'));
```

#### **Mock a GET response** (`mockClientGet()`)

Register a mock response for `Client::get()` calls matching the URL.

Arguments:
- `$url` (`string`): the request URL to match (supports `*` wildcards).
- `$response` (`Response`): the response to return.
- `$match` (`Closure(RequestInterface): bool|null`): an optional callback to accept/reject the request.

```php
use Psr\Http\Message\RequestInterface;

$response = $this->createResponse(200, [], 'OK');

$this->mockClientGet('http://localhost/*', $response, static function (RequestInterface $request): bool {
    return $request->getHeaderLine('X-Debug') === '1';
});

$result = $client->get('http://localhost/test', [], [
    'headers' => [
        'X-Debug' => '1',
    ],
]);

$this->assertSame('OK', (string) $result->getBody());
```

#### **Mock a POST response** (`mockClientPost()`)

Register a mock response for `Client::post()` calls matching the URL.

Arguments:
- `$url` (`string`): the request URL to match (supports `*` wildcards).
- `$response` (`Response`): the response to return.
- `$match` (`Closure(RequestInterface): bool|null`): an optional callback to accept/reject the request.

```php
$this->mockClientPost('http://localhost/test', $this->createResponse(201, [], 'Created'));

$result = $client->post('http://localhost/test');

$this->assertSame(201, $result->getStatusCode());
```

#### **Mock a PUT response** (`mockClientPut()`)

Register a mock response for `Client::put()` calls matching the URL.

Arguments:
- `$url` (`string`): the request URL to match (supports `*` wildcards).
- `$response` (`Response`): the response to return.
- `$match` (`Closure(RequestInterface): bool|null`): an optional callback to accept/reject the request.

```php
$this->mockClientPut('http://localhost/test', $this->createResponse(200, [], 'Updated'));

$result = $client->put('http://localhost/test');

$this->assertSame(200, $result->getStatusCode());
```

#### **Mock a PATCH response** (`mockClientPatch()`)

Register a mock response for `Client::patch()` calls matching the URL.

Arguments:
- `$url` (`string`): the request URL to match (supports `*` wildcards).
- `$response` (`Response`): the response to return.
- `$match` (`Closure(RequestInterface): bool|null`): an optional callback to accept/reject the request.

```php
$this->mockClientPatch('http://localhost/test', $this->createResponse(200, [], 'Patched'));

$result = $client->patch('http://localhost/test');

$this->assertSame(200, $result->getStatusCode());
```

#### **Mock a DELETE response** (`mockClientDelete()`)

Register a mock response for `Client::delete()` calls matching the URL.

Arguments:
- `$url` (`string`): the request URL to match (supports `*` wildcards).
- `$response` (`Response`): the response to return.
- `$match` (`Closure(RequestInterface): bool|null`): an optional callback to accept/reject the request.

```php
$this->mockClientDelete('http://localhost/test', $this->createResponse(204));

$result = $client->delete('http://localhost/test');

$this->assertSame(204, $result->getStatusCode());
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Mocking is global to `Client` (a static mock handler). The trait clears mocks after each test via `#[After]`.
- URL matching is exact by default, but `*` in the mock URL matches any character sequence.
- When a mock response matches, it is moved to the end of the internal list, which results in a round-robin rotation when multiple mocks match the same request.
- If you provide a match callback and it returns `false`, the next mock is checked instead.
- If no mock response matches, a `RuntimeException` is thrown with a message like `No mock response found for http://localhost/test (GET).`.
- Mocks affect `Client::send()` and the verb methods (`get()`, `post()`, …). `Client::sendRequest()` bypasses the mock handler.

## Related

- [Testing](index.md)
- [HTTP Client](../http/client.md)
