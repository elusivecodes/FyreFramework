# HTTP Client Testing

Use `HttpClientTestTrait` to replace outbound `Fyre\Http\Client` calls with deterministic responses. No network request is made when a mock matches.

## Table of Contents

- [Set up client mocks](#set-up-client-mocks)
- [Match requests](#match-requests)
- [Method guide](#method-guide)
  - [`HttpClientTestTrait`](#httpclienttesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Set up client mocks

Use the trait in a framework `TestCase`, create the response to return, and register it before running the code under test:

```php
use Fyre\Http\Client;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\HttpClientTestTrait;

final class UserApiTest extends TestCase
{
    use HttpClientTestTrait;

    public function testFetchesUser(): void
    {
        $response = $this->createResponse(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"id":1,"name":"Ada"}'
        );

        $this->mockClientGet(
            'https://api.example.com/users/1',
            $response
        );

        $result = new Client()
            ->get('https://api.example.com/users/1');

        $this->assertSame(
            200,
            $result->getStatusCode()
        );
        $this->assertSame(
            ['id' => 1, 'name' => 'Ada'],
            $result->getJson()
        );
    }
}
```

The trait clears all client mocks after each test.

## Match requests

URLs match exactly unless the registered URL contains `*`, which matches any character sequence. Add a callback when the URL and method are not enough to identify the request:

```php
use Psr\Http\Message\RequestInterface;

$response = $this->createResponse(202, [], 'Accepted');

$this->mockClientPost(
    'https://api.example.com/users/*',
    $response,
    static fn(RequestInterface $request): bool =>
        $request->getHeaderLine('Idempotency-Key') === 'create-user-1'
);

$result = $client->post(
    'https://api.example.com/users/1',
    [],
    [
        'headers' => [
            'Idempotency-Key' => 'create-user-1',
        ],
    ]
);

$this->assertSame(
    'Accepted',
    (string) $result->getBody()
);
```

The callback receives the PSR-7 request and must return `true` to select that mock. A rejected mock does not fail immediately; matching continues with the next registered response.

## Method guide

The setup above applies to every method in this trait.

### `HttpClientTestTrait`

#### **Create a client response** (`createResponse()`)

```php
createResponse(
    int $statusCode = 200,
    array $headers = [],
    string $body = ''
): Fyre\Http\Client\Response
```

`$headers` accepts string values or lists of strings. The returned response can be reused by more than one mock.

#### **Register responses by method** (`mockClientGet()`, `mockClientPost()`, `mockClientPut()`, `mockClientPatch()`, `mockClientDelete()`)

Every verb helper has the same arguments:

- `$url` (`string`): exact URL or a pattern containing `*` wildcards.
- `$response` (`Fyre\Http\Client\Response`): response returned when the mock matches.
- `$match` (`Closure|null`): optional request predicate.

| Request method | Helper |
| --- | --- |
| `GET` | `mockClientGet()` |
| `POST` | `mockClientPost()` |
| `PUT` | `mockClientPut()` |
| `PATCH` | `mockClientPatch()` |
| `DELETE` | `mockClientDelete()` |

## Behavior notes

- Client mocks are global and are cleared automatically after each test using the trait.
- Multiple mocks that match the same request rotate in round-robin order because a selected mock moves to the end of the list.
- A callback returning `false` allows later mocks to be considered.
- A request with no matching response throws a `RuntimeException`.
- Mocks affect `Client::send()` and the verb helpers; PSR-18 `Client::sendRequest()` bypasses the mock handler.

## Related

- [Testing](index.md)
- [HTTP Client](../http/client.md)
- [Integration Testing](integration.md)
