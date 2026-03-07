# CSRF

`Fyre\Security\CsrfProtection` generates and validates CSRF tokens for incoming requests.

Cross-Site Request Forgery (CSRF) protection prevents third-party sites from triggering state-changing requests as an authenticated user. In Fyre, CSRF protection ties a cookie token to a per-client secret and requires a matching user token (form field or header) on unsafe HTTP methods.

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Configuring CSRF](#configuring-csrf)
  - [Example `config/app.php`](#example-configappphp)
- [Middleware integration](#middleware-integration)
- [`FormHelper` integration](#formhelper-integration)
- [Manually embedding the token in HTML forms](#manually-embedding-the-token-in-html-forms)
- [Using the CSRF header (AJAX/JSON)](#using-the-csrf-header-ajaxjson)
  - [Example: send the token with `fetch()`](#example-send-the-token-with-fetch)
- [Method guide](#method-guide)
  - [`CsrfProtection`](#csrfprotection)
  - [`CsrfProtectionMiddleware`](#csrfprotectionmiddleware)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

CSRF protection is primarily about protecting requests that change server state (create/update/delete actions). With CSRF enabled, a request must provide:

- a CSRF cookie token issued by the server
- a matching “user token” sent either as a form field or as a request header

## Mental model

`CsrfProtection` works with two token representations:

- **Cookie token**: stored in a cookie and sent automatically by the browser on same-site requests.
- **Form/header token**: a salted form of the cookie token, safe to embed in HTML and to send back in requests.

On validation, the user-provided token is “unsalted” and compared against the cookie token using a constant-time comparison.

## Configuring CSRF

CSRF behavior is configured under the `Csrf` key in [Config](../core/config.md). The most important setting is `Csrf.salt`, which must be a stable, secret value.

If `Csrf.salt` changes, existing tokens can no longer be validated (and all clients will effectively “lose” their CSRF cookies until they refresh and get a new one).

If `Csrf.salt` is missing or empty, tokens are still generated but the construction is weaker than intended. Always set a real secret.

### Example `config/app.php`

```php
return [
    'Csrf' => [
        // Generate once and keep stable (for example: base64_encode(random_bytes(32))).
        'salt' => 'your-secret-here',

        // Cookie options for the CSRF cookie token.
        'cookie' => [
            // Use true in production (HTTPS). If you serve over plain HTTP in local dev,
            // set secure=false or the cookie won’t be sent.
            'secure' => true,
            'sameSite' => 'Lax',

            // If you don’t need JavaScript to read the CSRF cookie token, prefer httpOnly=true.
            'httpOnly' => true,
        ],
    ],
];
```

## Middleware integration

`CsrfProtectionMiddleware` enforces CSRF checks and makes the current `CsrfProtection` instance available to downstream middleware/handlers:

- The middleware calls `CsrfProtection::checkToken()` for the request.
- The middleware then calls `CsrfProtection::beforeResponse()` to ensure the CSRF cookie is sent when it’s missing.
- The `CsrfProtection` instance is attached to the request under the `csrf` attribute key.

In a typical application middleware queue, this middleware is commonly referenced using the default alias `csrf`.

That `csrf` request attribute is the normal way to access CSRF token metadata when rendering a response (including view helpers).

## `FormHelper` integration

When the request has a `csrf` attribute, `FormHelper::open()` automatically injects a hidden input containing the CSRF form token. This is the most common way to include CSRF tokens in HTML forms.

```php
use Fyre\View\View;

/** @var View $this */

echo $this->Form->open(null, [
    'method' => 'post',
    'action' => '/profile',
]);

echo $this->Form->text('display_name');
echo $this->Form->close();
```

## Manually embedding the token in HTML forms

If you are not using `FormHelper`, embed the salted token as a hidden input. After `CsrfProtectionMiddleware` has run, the request has a `csrf` attribute containing the current `CsrfProtection` instance.

```php
use Fyre\Security\CsrfProtection;
use Psr\Http\Message\ServerRequestInterface;

/** @var ServerRequestInterface $request */

$field = null;
$token = null;

$csrf = $request->getAttribute('csrf');
if ($csrf instanceof CsrfProtection) {
    $field = $csrf->getField();
    $token = $csrf->getFormToken();
}

if ($field && $token) {
    echo '<input type="hidden" name="'.htmlspecialchars($field, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">';
}
```

## Using the CSRF header (AJAX/JSON)

When a request body isn’t form-encoded (for example, JSON requests), send the salted token via the configured CSRF header name.

```php
use Fyre\Security\CsrfProtection;
use Psr\Http\Message\ServerRequestInterface;

function renderCsrfMeta(ServerRequestInterface $request): string
{
    $csrf = $request->getAttribute('csrf');
    if (!$csrf instanceof CsrfProtection) {
        return '';
    }

    $header = $csrf->getHeader();
    $token = $csrf->getFormToken();

    if (!$header || !$token) {
        return '';
    }

    return '<meta name="'.htmlspecialchars($header, ENT_QUOTES, 'UTF-8').'" content="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">';
}
```

Client-side code can then read the meta value and send it as the request header on `POST`, `PUT`, `PATCH`, and `DELETE` requests.

### Example: send the token with `fetch()`

This example uses the default header name (`Csrf-Token`). If you changed `Csrf.header`, update the header name and the meta selector.

```js
const meta = document.querySelector('meta[name="Csrf-Token"]');
const token = meta?.content;

await fetch('/profile', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Csrf-Token': token ?? '',
  },
  body: JSON.stringify({ display_name: 'Example' }),
});
```

## Method guide

Most examples assume you already have a `$request` instance (via dependency injection). After CSRF middleware has run, the request has a `csrf` request attribute that provides the `CsrfProtection` instance.

Examples below also assume the relevant CSRF classes are already imported when needed.

### `CsrfProtection`

#### **Get the form token** (`getFormToken()`)

Returns a salted token suitable for embedding in HTML or sending back via a header.

```php
$csrf = $request->getAttribute('csrf');
if ($csrf instanceof CsrfProtection) {
    $token = $csrf->getFormToken();
}
```

#### **Get the form field name** (`getField()`)

Returns the configured form field name used to read CSRF tokens from parsed request bodies.

```php
$csrf = $request->getAttribute('csrf');
if ($csrf instanceof CsrfProtection) {
    $field = $csrf->getField();
}
```

#### **Get the header name** (`getHeader()`)

Returns the configured request header name used to read CSRF tokens.

```php
$csrf = $request->getAttribute('csrf');
if ($csrf instanceof CsrfProtection) {
    $header = $csrf->getHeader();
}
```

#### **Validate a request** (`checkToken()`)

Attaches the `csrf` request attribute and, on state-changing methods, validates the request token.

Arguments:
- `$request` (`ServerRequestInterface`): the request to validate.

```php
$request = $csrf->checkToken($request);
```

#### **Add the CSRF cookie when needed** (`beforeResponse()`)

Ensures the CSRF cookie is included in the response when it was missing from the request.

Arguments:
- `$request` (`ServerRequestInterface`): the request being handled.
- `$response` (`ResponseInterface`): the response returned by downstream handlers.

```php
$response = $csrf->beforeResponse($request, $response);
```

#### **Get the cookie token** (`getCookieToken()`)

Returns the current cookie token, generating one if needed.

```php
$token = $csrf->getCookieToken();
```

### `CsrfProtectionMiddleware`

#### **Run CSRF checks in a middleware pipeline** (`process()`)

Validates the request and ensures the CSRF cookie behavior is applied to the response.

Arguments:
- `$request` (`ServerRequestInterface`): the request to validate.
- `$handler` (`RequestHandlerInterface`): the next pipeline handler.

```php
use Fyre\Security\Middleware\CsrfProtectionMiddleware;
use Fyre\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

$request = request();
$middleware = app(CsrfProtectionMiddleware::class);

$handler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
};

$response = $middleware->process($request, $handler);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Only `DELETE`, `PATCH`, `POST`, and `PUT` are checked by default; other methods still get the `csrf` request attribute.
- If the token is provided via the configured field name and the parsed body is an array, the token field is removed from the parsed body before it reaches the handler.
- CSRF protection cannot be enabled twice for the same request; calling `checkToken()` when a `csrf` request attribute already exists raises an exception.
- When CSRF checks are applied, validation requires both the cookie token and a user-provided token; if either is missing on a checked method, the request fails validation.
- A configured “skip check” callback can bypass validation for a request.
- If the field name or header name is disabled (set to `null`), that input channel is not considered during validation.

## Related

- [HTTP Middleware](../http/middleware.md) — middleware pipeline model, including the default `csrf` alias.
- [Forms](../view/forms.md) — using `FormHelper` to inject CSRF tokens into HTML forms.
