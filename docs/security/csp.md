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
  - [Updating policies](#updating-policies)
- [Applying headers](#applying-headers)
  - [Apply CSP headers to a response](#apply-csp-headers-to-a-response)
  - [Middleware integration](#middleware-integration)
- [Using nonces in views](#using-nonces-in-views)
- [Related](#related)

## Start here

Use CSP when you want to:

- restrict what scripts, styles, images, and other resources a page may load
- start in report-only mode before enforcing a policy
- generate nonces for inline scripts and styles in your views

In production, it’s common to start with report-only CSP, collect reports, then switch to enforced mode once you’re confident the policy won’t break real pages. Report-only policies report violations but do not block them.

When emitting headers, `ContentSecurityPolicy` can output:

- `Content-Security-Policy` (enforced)
- `Content-Security-Policy-Report-Only` (report-only)
- `Reporting-Endpoints` (when configured via `setReportingEndpoints()`)

The main pieces are:

- `Fyre\Security\ContentSecurityPolicy` builds and applies CSP headers
- `Fyre\Security\Policy` validates directives and formats header strings
- `Fyre\Security\Middleware\CspMiddleware` applies CSP at the HTTP middleware boundary
- `Fyre\View\Helpers\CspHelper` generates nonces in templates and updates configured policies

## Configuring CSP

CSP is configured under the `Csp` key in [Config](../core/config.md). `ContentSecurityPolicy` reads `Csp.default` and `Csp.report` automatically at construction time, and emits them as:

- `Csp.default` is emitted as `Content-Security-Policy` (enforced)
- `Csp.report` is emitted as `Content-Security-Policy-Report-Only` (report-only)

Only policies stored under the `default` and `report` keys are emitted. You can store policies under other keys for application use, but `addHeaders()` will not add them to a response.

If neither key is configured, no CSP policy header is emitted.

If you want browsers to send CSP violation reports, configure:

- a `report-to` directive in the policy that references a group name
- `Csp.reportingEndpoints` so `ContentSecurityPolicy` can emit the `Reporting-Endpoints` header for that group

You can also include `report-uri` for compatibility with older reporting implementations.

`Csp.reportingEndpoints` is passed to `setReportingEndpoints()` during construction. With a `ContentSecurityPolicy` instance in `$csp`, you can also replace the endpoints at runtime:

```php
$csp->setReportingEndpoints([
    'csp' => 'https://reports.example.com/csp',
]);
```

This only controls the `Reporting-Endpoints` header. It does not add a matching `report-to` directive to either policy.

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

Unknown directive names raise an `InvalidArgumentException` when the policy is created or updated.

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

### Updating policies

`Policy` is immutable. `withDirective()` and `withoutDirective()` return a new instance, so store the updated policy back on `ContentSecurityPolicy` when it should affect emitted headers:

```php
use Fyre\Security\ContentSecurityPolicy;

$policy = $csp->getPolicy(ContentSecurityPolicy::DEFAULT);

if ($policy) {
    $policy = $policy
        ->withDirective('connect-src', ['self', 'https://api.example.com'])
        ->withoutDirective('upgrade-insecure-requests');

    $csp->setPolicy(ContentSecurityPolicy::DEFAULT, $policy);
}
```

Passing `false` to `withDirective()` also removes the directive. Existing values are retained and duplicate values are ignored. Use `getHeaderString()` or cast a policy to `string` when you need to inspect the formatted header value directly.

## Applying headers

Applying CSP is just a response header operation. `ContentSecurityPolicy::addHeaders()` returns a new response instance with any configured CSP headers added.

Policies with no directives produce an empty header string and are not emitted. `Reporting-Endpoints` is emitted only when at least one endpoint is configured.

### Apply CSP headers to a response

```php
$response = $csp->addHeaders($response);
```

### Middleware integration

`CspMiddleware` applies CSP headers to the response returned by the next handler. This keeps CSP enforcement centralized at the HTTP boundary while still allowing templates and handlers to adjust policies before the response is returned.

For response behavior and emission details, see [HTTP Responses](../http/responses.md).

## Using nonces in views

`CspHelper` generates nonces for inline `<script>` and `<style>` blocks and adds them to all policies currently stored on the `ContentSecurityPolicy` instance. It updates the stored policies rather than acting as a pure formatter.

For view helper basics, see [Helpers](../view/helpers.md). For a focused overview of `CspHelper`, see [CSP helper](../view/helpers.md#csp-helper).

The helper returns the raw nonce value; use it in the HTML `nonce` attribute:

```php
use Fyre\View\View;

/** @var View $this */

$nonce = $this->Csp->scriptNonce();

echo '<script nonce="'.htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8').'">console.log("ok");</script>';
```

`scriptNonce()` updates policies by adding a `nonce-...` value to the `script-src` directive the first time it is called, then reuses that nonce for the current helper instance. `styleNonce()` does the same for `style-src`.

Define a baseline `script-src` or `style-src` yourself, such as one containing `self`. Adding either directive changes CSP fallback behavior by overriding `default-src`. If no policies exist, the helper still returns a nonce, but there is no policy to update and the nonce will not appear in an emitted header.

## Related

- [HTTP Responses](../http/responses.md) - work with response headers and emission
- [Helpers](../view/helpers.md) - view helper fundamentals, including `CspHelper`
