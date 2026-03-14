# HTTP Client Testing

Use `HttpClientTestTrait` when your code calls `Fyre\Http\Client` and you want tests without real network traffic.

The trait lets you register mock responses for common HTTP verbs and clears them automatically after each test.

## Table of Contents

- [Start here](#start-here)
- [Mocking responses](#mocking-responses)
- [Matching requests](#matching-requests)
- [Method guide](#method-guide)
  - [`HttpClientTestTrait`](#httpclienttesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Create a mock `Response`.
2. Register it for the verb and URL you expect.
3. Run the code that uses `Client`.
4. Assert against the returned response.

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

## Mocking responses

Use the verb-specific helpers to register mock responses:

- `mockClientGet()`
- `mockClientPost()`
- `mockClientPut()`
- `mockClientPatch()`
- `mockClientDelete()`

Each helper takes a URL, a `Response`, and an optional match callback.

## Matching requests

URL matching is exact by default, but `*` acts as a wildcard. If you need more control, pass a match callback that receives the `RequestInterface` and returns `true` only for requests you want that mock to handle.

## Method guide

Most examples assume you’re in a `TestCase` using `HttpClientTestTrait`, and you already have a `$client` instance.

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

A few behaviors are worth keeping in mind:

- Mocking is global to `Client`, and the trait clears mocks after each test.
- URL matching is exact by default, but `*` in the mock URL matches any character sequence.
- When a mock response matches, it is moved to the end of the internal list, so multiple matching mocks rotate in round-robin order.
- If you provide a match callback and it returns `false`, the next mock is checked instead.
- If no mock response matches, a `RuntimeException` is thrown.
- Mocks affect `Client::send()` and the verb methods (`get()`, `post()`, …). `Client::sendRequest()` bypasses the mock handler.

## Related

- [Testing](index.md)
- [HTTP Client](../http/client.md)
