# Content Security Policy (CSP)

Use `Fyre\Security\ContentSecurityPolicy` when you want to build and apply Content Security Policy headers to your responses.

CSP restricts what a page is allowed to load and is commonly applied through middleware, with optional nonce support in views.

## Table of Contents

- [Start here](#start-here)
- [Configuring CSP](#configuring-csp)
  - [Example `config/app.php`](#example-configappphp)
- [Building policies](#building-policies)
  - [Create an enforced policy](#create-an-enforced-policy)
  - [Create a report-only policy](#create-a-report-only-policy)
  - [Source value formatting](#source-value-formatting)
- [Applying headers](#applying-headers)
  - [Apply CSP headers to a response](#apply-csp-headers-to-a-response)
  - [Middleware integration](#middleware-integration)
- [Using nonces in views](#using-nonces-in-views)
- [Method guide](#method-guide)
  - [`ContentSecurityPolicy`](#contentsecuritypolicy)
  - [`Policy`](#policy)
  - [`CspMiddleware`](#cspmiddleware)
  - [`CspHelper`](#csphelper)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use CSP when you want to:

- restrict what scripts, styles, images, and other resources a page may load
- start in report-only mode before enforcing a policy
- generate nonces for inline scripts and styles in your views

In production, it’s common to start with report-only CSP, collect reports, then switch to enforced mode once you’re confident the policy won’t break real pages.

When emitting headers, `ContentSecurityPolicy` can output:

- `Content-Security-Policy` (enforced)
- `Content-Security-Policy-Report-Only` (report-only)
- `Reporting-Endpoints` (when configured via `setReportingEndpoints()`)

This is typically used through:

- `Fyre\Security\ContentSecurityPolicy`: builds and applies CSP headers.
- `Fyre\Security\Policy`: validates directives and formats header strings.
- `Fyre\Security\Middleware\CspMiddleware`: applies CSP at the HTTP middleware boundary.
- `Fyre\View\Helpers\CspHelper`: generates nonces in templates and updates configured policies.

## Configuring CSP

CSP is configured under the `Csp` key in [Config](../core/config.md). `ContentSecurityPolicy` reads `Csp.default` and `Csp.report` automatically at construction time, and emits them as:

- `Csp.default` is emitted as `Content-Security-Policy` (enforced)
- `Csp.report` is emitted as `Content-Security-Policy-Report-Only` (report-only)

If you want browsers to send CSP violation reports, configure:

- a `report-to` directive in the policy that references a group name
- `Csp.reportingEndpoints` so `ContentSecurityPolicy` can emit the `Reporting-Endpoints` header for that group

You can also include `report-uri` for compatibility with older reporting implementations.

### Example `config/app.php`

```php
return [
    'Csp' => [
        'default' => [
            'default-src' => ['self'],
            'img-src' => ['self', 'https://cdn.example.com'],
        ],
        'report' => [
            'default-src' => ['self'],
            'report-to' => 'csp',
            'report-uri' => 'https://reports.example.com/csp',
        ],
        'reportingEndpoints' => [
            'csp' => 'https://reports.example.com/csp',
        ],
    ],
];
```

## Building policies

Policies are built from a directive map:

- keys are directive names (validated against a known directive list)
- values may be:
  - `true` to include the directive with no values (useful for boolean directives)
  - `false` to omit the directive entirely
  - a `string` or `string[]` of directive values

Most examples assume you already have a `$csp` instance (via dependency injection or `app(ContentSecurityPolicy::class)`).

### Create an enforced policy

```php
use Fyre\Security\ContentSecurityPolicy;
use Fyre\Security\Policy;

$policy = new Policy([
    'default-src' => ['self'],
    'img-src' => ['self', 'https://cdn.example.com'],
    'upgrade-insecure-requests' => true,
]);

$csp->setPolicy(ContentSecurityPolicy::DEFAULT, $policy);
```

### Create a report-only policy

```php
use Fyre\Security\ContentSecurityPolicy;

$csp->createPolicy(ContentSecurityPolicy::REPORT, [
    'default-src' => ['self'],
    'report-uri' => 'https://reports.example.com/csp',
]);
```

### Source value formatting

When a `Policy` is converted to a header string, common source keywords are automatically quoted (for example `self` becomes `'self'`). Nonces and hashes are also quoted when provided in `nonce-...` or `sha256-...` / `sha384-...` / `sha512-...` form.

Pass source keywords and nonces without quotes:

- Use `self`
- Use `nonce-<value>`
- Don’t include quotes (for example, use `self`, not `'self'`)

## Applying headers

Applying CSP is just a response header operation. `ContentSecurityPolicy::addHeaders()` returns a new response instance with any configured CSP headers added.

### Apply CSP headers to a response

```php
$response = $csp->addHeaders($response);
```

### Middleware integration

`CspMiddleware` applies CSP headers to the response returned by the next handler. This keeps CSP enforcement centralized at the HTTP boundary while still allowing templates and handlers to adjust policies before the response is returned.

For response behavior and emission details, see [HTTP Responses](../http/responses.md).

## Using nonces in views

`CspHelper` generates per-render nonces for inline `<script>` and `<style>` blocks and adds those nonces to all policies currently stored on the `ContentSecurityPolicy` instance.

For view helper basics, see [Helpers](../view/helpers.md). For a focused overview of `CspHelper`, see [CSP helper](../view/helpers.md#csp-helper).

The helper returns the raw nonce value; use it in the HTML `nonce` attribute:

```php
use Fyre\View\View;

/** @var View $this */

$nonce = $this->Csp->scriptNonce();

echo '<script nonce="'.htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8').'">console.log("ok");</script>';
```

`scriptNonce()` updates policies by adding a `nonce-...` value to the `script-src` directive the first time it is called, then reuses that nonce for the current helper instance. `styleNonce()` does the same for `style-src`.

## Method guide

### `ContentSecurityPolicy`

#### **Create and store a policy** (`createPolicy()`)

Creates a `Policy` from a directive map and stores it under the provided key.

Arguments:
- `$key` (`string`): the policy key (only `default` and `report` are emitted as headers).
- `$directives` (`array`): the directive map.

```php
use Fyre\Security\ContentSecurityPolicy;

$csp->createPolicy(ContentSecurityPolicy::REPORT, [
    'default-src' => ['self'],
    'report-uri' => 'https://reports.example.com/csp',
]);
```

#### **Set (or replace) a policy** (`setPolicy()`)

Stores a `Policy` instance under the provided key.

Arguments:
- `$key` (`string`): the policy key.
- `$policy` (`Policy`): the policy instance.

```php
use Fyre\Security\ContentSecurityPolicy;
use Fyre\Security\Policy;

$policy = new Policy([
    'default-src' => ['self'],
    'img-src' => ['self', 'https://cdn.example.com'],
]);

$csp->setPolicy(ContentSecurityPolicy::DEFAULT, $policy);
```

#### **Add CSP headers to a response** (`addHeaders()`)

Returns a new response with any configured CSP headers applied.

Arguments:
- `$response` (`ResponseInterface`): the response to add headers to.

```php
$response = $csp->addHeaders($response);
```

#### **Configure reporting endpoints** (`setReportingEndpoints()`)

Sets the named endpoints emitted in the `Reporting-Endpoints` header when non-empty.

Arguments:
- `$reportingEndpoints` (`array<string, string>`): the endpoint names and URLs to emit.

`setReportingEndpoints()` only affects the `Reporting-Endpoints` response header. To make CSP use an endpoint for violation reporting, also set a `report-to` directive in your CSP policy with the same name (for example `'report-to' => 'csp'`).

```php
use Fyre\Security\ContentSecurityPolicy;

$csp->setReportingEndpoints([
    'csp' => 'https://reports.example.com/csp',
]);
```

### `Policy`

#### **Add values to a directive** (`withDirective()`)

Returns a cloned policy with additional values added to the directive. Known source keywords, nonces, and hashes are automatically quoted when formatting the header string.

Arguments:
- `$directive` (`string`): the directive name.
- `$value` (`array|bool|string`): the value(s) to add, `true` for an empty directive, or `false` to remove the directive.

```php
use Fyre\Security\Policy;

$policy = new Policy(['default-src' => ['self']]);
$policy = $policy->withDirective('img-src', ['self', 'https://cdn.example.com']);
```

#### **Remove a directive** (`withoutDirective()`)

Returns a cloned policy without the specified directive.

Arguments:
- `$directive` (`string`): the directive name.

```php
use Fyre\Security\Policy;

$policy = new Policy(['default-src' => ['self'], 'frame-ancestors' => ['none']]);
$policy = $policy->withoutDirective('frame-ancestors');
```

#### **Format the header value** (`getHeaderString()`)

Returns the CSP header value string for this directive set.

```php
use Fyre\Security\Policy;

$policy = new Policy(['default-src' => ['self']]);
$value = $policy->getHeaderString();
```

### `CspMiddleware`

#### **Apply CSP headers as middleware** (`process()`)

Delegates to the next handler and applies CSP headers to the returned response.

Arguments:
- `$request` (`ServerRequestInterface`): the incoming request.
- `$handler` (`RequestHandlerInterface`): the next handler in the chain.

```php
use Fyre\Security\Middleware\CspMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @var CspMiddleware $middleware */
/** @var ServerRequestInterface $request */
/** @var RequestHandlerInterface $handler */

$response = $middleware->process($request, $handler);
```

### `CspHelper`

#### **Generate a script nonce** (`scriptNonce()`)

Returns the script nonce for the current helper instance and ensures it is present in stored policies under `script-src`.

```php
use Fyre\View\View;

/** @var View $this */

$nonce = $this->Csp->scriptNonce();
echo '<script nonce="'.$nonce.'"></script>';
```

#### **Generate a style nonce** (`styleNonce()`)

Returns the style nonce for the current helper instance and ensures it is present in stored policies under `style-src`.

```php
use Fyre\View\View;

/** @var View $this */

$nonce = $this->Csp->styleNonce();
echo '<style nonce="'.$nonce.'">body{}</style>';
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Only policies with keys `default` and `report` are emitted as headers; other stored policies are not added by `addHeaders()`.
- A policy with no directives produces an empty header string and is not emitted.
- Unknown directive names raise an `InvalidArgumentException`.
- `Reporting-Endpoints` is emitted only when `setReportingEndpoints()` has been called with a non-empty array; it does not automatically add a `report-to` directive to your policies.
- `CspHelper` mutates the `ContentSecurityPolicy` instance by updating stored policies in place (it is not a “pure” formatter).
- When you enable nonces via `CspHelper`, define a baseline `script-src` / `style-src` yourself (for example including `self`). Adding `script-src` can change CSP fallback behavior by overriding `default-src`.
- `scriptNonce()` and `styleNonce()` reuse the same nonce for repeated calls on the current helper instance, so you can call them from multiple partials within a render without appending duplicate nonce values.
- If no policies exist on the `ContentSecurityPolicy` instance, `CspHelper` still returns a nonce but has no policies to update, so the nonce will not appear in emitted headers.

## Related

- [HTTP Responses](../http/responses.md) - work with response headers and emission
- [Helpers](../view/helpers.md) - view helper fundamentals, including `CspHelper`
