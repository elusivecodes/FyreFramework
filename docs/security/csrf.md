# CSRF

Use `Fyre\Security\CsrfProtection` when you want to protect state-changing requests with CSRF tokens.

In Fyre, CSRF protection uses a cookie token plus a matching form field or request header on unsafe HTTP methods.

## Table of Contents

- [Start here](#start-here)
- [Configuring CSRF](#configuring-csrf)
  - [Options](#options)
  - [Example `config/app.php`](#example-configappphp)
- [Middleware integration](#middleware-integration)
  - [Validation failures](#validation-failures)
- [`FormHelper` integration](#formhelper-integration)
- [Manually embedding the token in HTML forms](#manually-embedding-the-token-in-html-forms)
- [Using the CSRF header (AJAX/JSON)](#using-the-csrf-header-ajaxjson)
  - [Example: send the token with `fetch()`](#example-send-the-token-with-fetch)
- [Related](#related)

## Start here

Use CSRF protection when you want to:

- protect form posts and other unsafe requests from third-party sites
- send a hidden token in HTML forms or a header in AJAX and JSON requests
- keep token handling centralized in middleware and form helpers

With CSRF enabled, a checked request must provide:

- **Cookie token**: stored in a cookie and sent automatically by the browser on same-site requests.
- **Form/header token**: a salted form of the cookie token, safe to embed in HTML and to send back in requests.

Only `DELETE`, `PATCH`, `POST`, and `PUT` are checked by default. Other methods still initialize CSRF protection and can issue the cookie needed by a later state-changing request.

## Configuring CSRF

CSRF behavior is configured under the `Csrf` key in [Config](../core/config.md). The most important setting is `Csrf.salt`, which must be a stable, secret value.

Generate the salt once, load it from your application secrets, and do not commit the deployed value.

Changing `Csrf.salt` invalidates existing tokens. Clients must remove or expire the old CSRF cookie before the middleware will issue a replacement.

If `Csrf.salt` is missing or empty, constructing `CsrfProtection` raises an `InvalidArgumentException`.

### Options

- `salt` (`string`): stable secret used to authenticate tokens; this option is required.
- `field` (`string|null`): parsed-body field used to read the form token (default: `csrf_token`); use `null` to disable form-field tokens.
- `header` (`string|null`): request header used to read the form token (default: `Csrf-Token`); use `null` to disable header tokens.
- `skipCheck` (`Closure|null`): callback that receives the request through the container and bypasses validation only when it returns `true`.
- `cookie.name` (`string`): cookie name (default: `CsrfToken`).
- `cookie.expires` (`int`): lifetime in seconds, or `0` for a session cookie (default: `0`).
- `cookie.domain` (`string`): cookie domain (default: an empty string).
- `cookie.path` (`string`): cookie path (default: `/`).
- `cookie.secure` (`bool`): whether the cookie is restricted to HTTPS (default: `true`).
- `cookie.httpOnly` (`bool`): whether JavaScript is prevented from reading the cookie (default: `false`).
- `cookie.sameSite` (`string`): SameSite policy (default: `Lax`).

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

`CsrfProtectionMiddleware` enforces CSRF checks and makes the current `CsrfProtection` instance available to downstream code:

- `checkToken()` attaches the `CsrfProtection` instance to the request under the `csrf` attribute key
- checked methods require a valid cookie token and a matching token from the configured form field or header
- `beforeResponse()` adds the CSRF cookie when it was missing from the request

In a typical application middleware queue, this middleware is commonly referenced using the default alias `csrf`.

That `csrf` request attribute is the normal way to access CSRF token metadata when rendering a response (including view helpers).

If the token comes from the configured form field and the parsed body is an array, `checkToken()` removes that field before passing the request downstream. Do not register CSRF protection twice for the same request; a second call raises a `BadMethodCallException`.

### Validation failures

Missing, malformed, or mismatched tokens on a checked method raise `CsrfTokenException`, which is a `403 Forbidden` HTTP exception. A `skipCheck` callback bypasses this validation for matching requests, but CSRF is still attached to the request.

Keep `skipCheck` narrowly scoped. Any unsafe request it matches proceeds without CSRF validation.

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
    if (!($csrf instanceof CsrfProtection)) {
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

## Related

- [HTTP Middleware](../http/middleware.md) - register the default `csrf` alias
- [Forms](../view/forms.md) - use `FormHelper` to inject CSRF tokens into HTML forms
