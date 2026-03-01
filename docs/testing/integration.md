# Integration Testing

`IntegrationTestTrait` provides request helpers and PHPUnit assertions for exercising your application through its HTTP stack and asserting against the captured response.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Making requests](#making-requests)
- [Setting request state](#setting-request-state)
  - [Cookies](#cookies)
  - [Session](#session)
  - [JSON requests](#json-requests)
  - [CSRF](#csrf)
- [Asserting the response](#asserting-the-response)
- [Method guide](#method-guide)
  - [Requests](#requests)
  - [Request state](#request-state)
  - [Response status and body assertions](#response-status-and-body-assertions)
  - [Header and redirect assertions](#header-and-redirect-assertions)
  - [Content type, cookies, and files](#content-type-cookies-and-files)
  - [Session assertions](#session-assertions)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `IntegrationTestTrait` when you want to send in-process requests (GET/POST/etc.) through the framework’s request handler and make assertions about status codes, response bodies, headers, redirects, cookies, and session state.

This trait is designed to be used in a `Fyre\TestSuite\TestCase` (it relies on the test case’s application engine via `$this->app`).

## Quick start

```php
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\IntegrationTestTrait;

final class ResponseTest extends TestCase
{
    use IntegrationTestTrait;

    public function testResponseBody(): void
    {
        $this->get('/response');

        $this->assertResponseOk();
        $this->assertResponseContains('test response');
    }
}
```

## Making requests

Use the request helpers to send an in-process request to your application and capture the last response:

- `get($path)`, `head($path)`, `options($path)`
- `post($path, $data)`, `put($path, $data)`, `patch($path, $data)`
- `delete($path)`

Query strings in the path are parsed and passed through as GET parameters:

```php
$this->get('/search?q=fyre');
```

## Setting request state

`IntegrationTestTrait` keeps per-test request state that is applied to the next request.

### Cookies

Use `cookie()` to add request cookies:

```php
$this->cookie('locale', 'en_US');
$this->get('/settings');
```

### Session

Use `session()` to set session data for the next request (it merges recursively into the existing session state):

```php
$this->session([
    'Auth' => [
        'user_id' => 1,
    ],
]);

$this->get('/account');
```

### JSON requests

Use `requestAsJson()` to set `Accept: application/json` and `Content-Type: application/json`. When you send `post()`/`put()`/`patch()` with a non-empty `$data` array, the trait JSON-encodes the data into the request body:

```php
$this->requestAsJson();
$this->post('/users', ['name' => 'Test User']);
```

### CSRF

Use `enableCsrfToken()` to populate the CSRF cookie and header for the next request, using the framework’s `CsrfProtection` service:

```php
$this->enableCsrfToken();
$this->post('/posts', ['title' => 'Test']);
```

## Asserting the response

After sending a request, the trait stores the last response and exposes assertion helpers for:

- status codes (`assertResponseOk()`, `assertResponseCode()`, `assertResponseError()`, …)
- response body (`assertResponseContains()`, `assertResponseEquals()`, …)
- headers and redirects (`assertHeader()`, `assertRedirectEquals()`, …)
- content type, cookies, and file responses (`assertContentType()`, `assertCookieIsSet()`, `assertFileResponse()`)
- session values and flash messages (`assertSession()`, `assertFlashMessage()`)

## Method guide

Most examples assume you’re in a `TestCase` using `IntegrationTestTrait`.

### Requests

#### **Send a GET request** (`get()`)

Sends a GET request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.

```php
$this->get('/health');
```

#### **Send a POST request** (`post()`)

Sends a POST request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.
- `$data` (`array<string, mixed>`): the request data.

```php
$this->post('/login', ['email' => 'user@example.com']);
```

#### **Send a PUT request** (`put()`)

Sends a PUT request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.
- `$data` (`array<string, mixed>`): the request data.

```php
$this->put('/profile', ['name' => 'Updated']);
```

#### **Send a PATCH request** (`patch()`)

Sends a PATCH request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.
- `$data` (`array<string, mixed>`): the request data.

```php
$this->patch('/profile', ['name' => 'Updated']);
```

#### **Send a DELETE request** (`delete()`)

Sends a DELETE request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.

```php
$this->delete('/sessions/current');
```

#### **Send a HEAD request** (`head()`)

Sends a HEAD request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.

```php
$this->head('/download');
```

#### **Send an OPTIONS request** (`options()`)

Sends an OPTIONS request to the application and stores the captured response.

Arguments:
- `$path` (`string`): the request path.

```php
$this->options('/api');
```

### Request state

#### **Set a request cookie** (`cookie()`)

Adds a cookie that will be included in the next request.

Arguments:
- `$name` (`string`): the cookie name.
- `$value` (`string`): the cookie value.

```php
$this->cookie('locale', 'en_US');
$this->get('/settings');
```

#### **Set session data** (`session()`)

Sets session data for the next request. Repeated calls merge recursively.

Arguments:
- `$data` (`array<string, mixed>`): the session data to merge.

```php
$this->session(['Auth' => ['user_id' => 1]]);
$this->get('/account');
```

#### **Mark the next request as JSON** (`requestAsJson()`)

Sets `Accept: application/json` and `Content-Type: application/json` for the next request.

```php
$this->requestAsJson();
$this->get('/api/health');
```

#### **Enable CSRF token for the next request** (`enableCsrfToken()`)

Populates the CSRF cookie and header for the next request using the framework’s CSRF protection service.

Arguments:
- `$cookieName` (`string`): the name of the CSRF token cookie.

```php
$this->enableCsrfToken();
$this->post('/posts', ['title' => 'Test']);
```

### Response status and body assertions

#### **Assert a specific status code** (`assertResponseCode()`)

Asserts that the last response has the expected status code.

Arguments:
- `$code` (`int`): the expected status code.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/missing');
$this->assertResponseCode(404);
```

#### **Assert the response is OK** (`assertResponseOk()`)

Asserts that the last response status code is between 200 and 204.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/health');
$this->assertResponseOk();
```

#### **Assert the response is successful** (`assertResponseSuccess()`)

Asserts that the last response status code is between 200 and 308.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/maybe-redirects');
$this->assertResponseSuccess();
```

#### **Assert the response is an error** (`assertResponseError()`)

Asserts that the last response status code is between 400 and 599.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/missing');
$this->assertResponseError();
```

#### **Assert the response is a failure** (`assertResponseFailure()`)

Asserts that the last response status code is between 500 and 505.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/error');
$this->assertResponseFailure();
```

#### **Assert the response body contains text** (`assertResponseContains()`)

Asserts that the last response body contains a string.

Arguments:
- `$needle` (`string`): the string to search for.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/response');
$this->assertResponseContains('test response');
```

#### **Assert the response body does not contain text** (`assertResponseNotContains()`)

Asserts that the last response body does not contain a string.

Arguments:
- `$needle` (`string`): the string to search for.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/response');
$this->assertResponseNotContains('unexpected');
```

#### **Assert the response body equals expected contents** (`assertResponseEquals()`)

Asserts that the last response body equals the expected string.

Arguments:
- `$body` (`string`): the expected response body.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/response');
$this->assertResponseEquals('exact response');
```

#### **Assert the response body does not equal expected contents** (`assertResponseNotEquals()`)

Asserts that the last response body does not equal the expected value.

Arguments:
- `$body` (`mixed`): the value to compare against.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/response');
$this->assertResponseNotEquals('not this');
```

#### **Assert the response body is empty** (`assertResponseEmpty()`)

Asserts that the last response body is empty.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/no-content');
$this->assertResponseEmpty();
```

#### **Assert the response body is not empty** (`assertResponseNotEmpty()`)

Asserts that the last response body is not empty.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/response');
$this->assertResponseNotEmpty();
```

### Header and redirect assertions

#### **Assert a header equals a value** (`assertHeader()`)

Asserts that the last response contains a header with the expected value.

Arguments:
- `$value` (`string`): the expected header value.
- `$header` (`string`): the header name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/download');
$this->assertHeader('application/pdf', 'Content-Type');
```

#### **Assert a header contains a value** (`assertHeaderContains()`)

Asserts that the last response contains a header value that includes the expected substring.

Arguments:
- `$value` (`string`): the substring to search for.
- `$header` (`string`): the header name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/download');
$this->assertHeaderContains('application/', 'Content-Type');
```

#### **Assert a header does not contain a value** (`assertHeaderNotContains()`)

Asserts that the last response does not have a header value containing the expected substring.

Arguments:
- `$value` (`string`): the substring to check for.
- `$header` (`string`): the header name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/download');
$this->assertHeaderNotContains('text/html', 'Content-Type');
```

#### **Assert the response is a redirect** (`assertRedirect()`)

Asserts that the last response has a `Location` header set.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/redirects');
$this->assertRedirect();
```

#### **Assert the response is not a redirect** (`assertNoRedirect()`)

Asserts that the last response does not have a `Location` header set.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->get('/health');
$this->assertNoRedirect();
```

#### **Assert the redirect equals a URL** (`assertRedirectEquals()`)

Asserts that the last response `Location` header equals the expected URL.

Arguments:
- `$url` (`string`): the expected redirect URL.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/redirects');
$this->assertRedirectEquals('/login');
```

#### **Assert the redirect contains a URL** (`assertRedirectContains()`)

Asserts that the last response `Location` header contains the expected substring.

Arguments:
- `$url` (`string`): the substring to search for.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/redirects');
$this->assertRedirectContains('/login');
```

#### **Assert the redirect does not contain a URL** (`assertRedirectNotContains()`)

Asserts that the last response `Location` header does not contain the expected substring.

Arguments:
- `$url` (`string`): the substring to check for.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/redirects');
$this->assertRedirectNotContains('/login');
```

### Content type, cookies, and files

#### **Assert the content type** (`assertContentType()`)

Asserts that the response `Content-Type` matches the expected type.

Arguments:
- `$type` (`string`): the expected content type.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/download');
$this->assertContentType('application/pdf');
```

#### **Assert a cookie equals a value** (`assertCookie()`)

Asserts that the response contains a cookie with the expected value.

Arguments:
- `$value` (`string`): the expected cookie value.
- `$name` (`string`): the cookie name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/sets-cookie');
$this->assertCookie('en_US', 'locale');
```

#### **Assert a cookie is set** (`assertCookieIsSet()`)

Asserts that the response contains a cookie with the given name.

Arguments:
- `$name` (`string`): the cookie name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/sets-cookie');
$this->assertCookieIsSet('locale');
```

#### **Assert a cookie is not set** (`assertCookieNotSet()`)

Asserts that the response does not contain a cookie with the given name.

Arguments:
- `$name` (`string`): the cookie name.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/no-cookie');
$this->assertCookieNotSet('locale');
```

#### **Assert a file download response** (`assertFileResponse()`)

Asserts that the response represents a file download with the expected path.

Arguments:
- `$path` (`string`): the expected file path.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/download');
$this->assertFileResponse('/path/to/file.pdf');
```

### Session assertions

#### **Assert a session key equals a value** (`assertSession()`)

Asserts that a session key has the expected value.

Arguments:
- `$value` (`mixed`): the expected session value.
- `$path` (`string`): the session key path.
- `$message` (`string`): the message to display on failure.

```php
$this->session(['Auth' => ['user_id' => 1]]);
$this->get('/account');

$this->assertSession(1, 'Auth.user_id');
```

#### **Assert a session key exists** (`assertSessionHasKey()`)

Asserts that a session key exists.

Arguments:
- `$path` (`string`): the session key path.
- `$message` (`string`): the message to display on failure.

```php
$this->session(['Auth' => ['user_id' => 1]]);
$this->get('/account');

$this->assertSessionHasKey('Auth.user_id');
```

#### **Assert a session key does not exist** (`assertSessionNotHasKey()`)

Asserts that a session key does not exist.

Arguments:
- `$path` (`string`): the session key path.
- `$message` (`string`): the message to display on failure.

```php
$this->get('/public');
$this->assertSessionNotHasKey('Auth.user_id');
```

#### **Assert a flash message equals a value** (`assertFlashMessage()`)

Asserts that a flash message exists in the session with the expected value.

Arguments:
- `$value` (`mixed`): the expected flash message value.
- `$key` (`string`): the flash message key.
- `$message` (`string`): the message to display on failure.

```php
$this->post('/form', ['submit' => true]);
$this->assertFlashMessage('Saved', 'default');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Response assertion helpers require a response; calling most `assert*()` methods before a request fails with “No response has been set.”
- Request state (`cookie()`, `session()`, `requestAsJson()`, `enableCsrfToken()`) is applied to the next request and cleared automatically after each test; set it again inside each test (or in `setUp()`).
- `session()` sets `$_SESSION` for the request, and session assertions read from `$_SESSION` (not the response).

## Related

- [Testing](index.md)
- [Fixtures](fixtures.md)
- [HTTP Client Testing](http-client.md)
