# Integration Testing

Use `IntegrationTestTrait` when you want to send in-process HTTP requests through your application and assert on the captured response.

It is a good fit for request and response testing, redirects, cookies, session state, and controller or middleware flows that should run through the full HTTP stack.

## Table of Contents

- [Start here](#start-here)
- [Making requests](#making-requests)
- [Setting request state](#setting-request-state)
  - [Cookies](#cookies)
  - [Session](#session)
  - [Request data](#request-data)
  - [Uploaded files](#uploaded-files)
  - [JSON requests](#json-requests)
  - [CSRF](#csrf)
- [Method guide](#method-guide)
  - [Status and body assertions](#status-and-body-assertions)
  - [Header and redirect assertions](#header-and-redirect-assertions)
  - [Content type, cookie, and file assertions](#content-type-cookie-and-file-assertions)
  - [Session assertions](#session-assertions)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Use `IntegrationTestTrait` in a `TestCase`.
2. Set any request state you need such as session, cookies, JSON headers, or CSRF tokens.
3. Send a request with `get()`, `post()`, or the other verb helpers.
4. Assert on the last captured response.

```php
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\IntegrationTestTrait;

final class ResponseTest extends TestCase
{
    use IntegrationTestTrait;

    public function testResponseBody(): void
    {
        $this->get('/health');

        $this->assertResponseCode(200);
        $this->assertResponseEquals('OK');
    }
}
```

## Making requests

Use the request helpers to send an in-process request to your application and capture the last response:

- `get($path)`, `head($path)`, `options($path)`
- `post($path, $data)`, `put($path, $data)`, `patch($path, $data)`
- `delete($path)`

The helpers return `void`; assertions operate on the captured response.

Query strings in the path are parsed and passed through as GET parameters:

```php
$this->get('/search?q=fyre');
```

Non-JSON array data is URL-encoded by default, so parsed scalar values use the same string representation as PHP form data.

## Setting request state

`IntegrationTestTrait` keeps persistent request configuration for the current test. Body data and uploaded files are staged separately for the next request only.

### Cookies

Use `cookie()` to add request cookies:

```php
$this->cookie('locale', 'en_US');
$this->get('/settings');
```

### Session

Use `session()` to set session data for subsequent requests in the current test. Repeated calls merge recursively into the existing session state:

```php
$this->session([
    'Auth' => [
        'user_id' => 1,
    ],
]);

$this->get('/account');
```

### Request data

Use `data()` to add body data to the next request. Repeated calls merge recursively:

```php
$this->data(['active' => true]);
$this->post('/users', ['name' => 'Test User']);
```

Staged data takes precedence over values passed directly to the request method.
Use `data()` to add body data to a `DELETE` request.

### Uploaded files

Use `file()` to add a local file to the next request. The field name supports dot notation for nested uploads:

```php
$this->file(
    'profile.avatar',
    'tests/files/avatar.png',
    'avatar.png',
    'image/png'
);

$this->post('/profile', ['name' => 'Test User']);
```

`POST`, `PUT`, `PATCH`, and `DELETE` requests containing files default to `multipart/form-data`.
The source file is copied to a temporary location, so application code can move the upload without modifying the original file.

### JSON requests

Use `requestAsJson()` to set `Accept: application/json` and `Content-Type: application/json` for subsequent requests in the current test. The merged data for `POST`, `PUT`, `PATCH`, and `DELETE` requests is JSON-encoded into the request body. When no data is supplied, the body is `[]`:

```php
$this->requestAsJson();
$this->post('/users', ['name' => 'Test User']);
```

### CSRF

Use `enableCsrfToken()` to populate the CSRF cookie, field, and header configured by the framework’s `CsrfProtection` service. The form field applies to the next request, while the cookie and header remain part of the current test request state:

```php
$this->enableCsrfToken();
$this->post('/posts', ['title' => 'Test']);
```

## Method guide

The test class under [Start here](#start-here) provides the setup for these request and assertion helpers. After sending a request, every assertion accepts an optional final `$message` argument for the PHPUnit failure message.

### Status and body assertions

| Assertion | Checks |
| --- | --- |
| `assertResponseCode($code)` | exact status code |
| `assertResponseOk()` | status from 200 through 204 |
| `assertResponseSuccess()` | status from 200 through 308 |
| `assertResponseError()` | status from 400 through 599 |
| `assertResponseFailure()` | status from 500 through 599 |
| `assertResponseContains($needle)` | body contains a string |
| `assertResponseNotContains($needle)` | body does not contain a string |
| `assertResponseEquals($body)` | exact body contents |
| `assertResponseNotEquals($body)` | body differs from a string |
| `assertResponseEmpty()` | empty body |
| `assertResponseNotEmpty()` | non-empty body |

### Header and redirect assertions

| Assertion | Checks |
| --- | --- |
| `assertHeader($value, $header)` | exact header value |
| `assertHeaderContains($value, $header)` | header contains a string |
| `assertHeaderNotContains($value, $header)` | header does not contain a string |
| `assertRedirect()` | a `Location` header is present |
| `assertNoRedirect()` | no `Location` header is present |
| `assertRedirectEquals($url)` | exact redirect URL |
| `assertRedirectContains($url)` | redirect URL contains a string |
| `assertRedirectNotContains($url)` | redirect URL does not contain a string |

### Content type, cookie, and file assertions

| Assertion | Checks |
| --- | --- |
| `assertContentType($type)` | response content type |
| `assertCookie($value, $name)` | exact response cookie value |
| `assertCookieIsSet($name)` | response cookie is present |
| `assertCookieNotSet($name)` | response cookie is absent |
| `assertFileResponse($path)` | response represents the given file download |

### Session assertions

Session paths use dot notation, such as `Auth.user_id`.

| Assertion | Checks |
| --- | --- |
| `assertSession($value, $path)` | exact session value |
| `assertSessionHasKey($path)` | session path exists |
| `assertSessionNotHasKey($path)` | session path does not exist |
| `assertFlashMessage($value, $key)` | exact flash message value |

## Behavior notes

- Response assertion helpers require a response; calling most `assert*()` methods before a request fails with "No response has been set."
- Request state (`cookie()`, `session()`, `requestAsJson()`, and the CSRF cookie and header) persists across requests within the same test until you overwrite it. The trait clears it automatically after each test.
- Data and files added with `data()` and `file()` apply only to the next request and are cleared before it is sent.
- `IntegrationTestTrait` stores only the last response; each new request replaces the previous `$response`.
- `session()` sets `$_SESSION` for the request, and session assertions read from `$_SESSION` (not the response).

## Related

- [Testing](index.md)
- [Fixtures](fixtures.md)
- [HTTP Client Testing](http-client.md)
