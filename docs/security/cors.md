# Cross-Origin Resource Sharing (CORS)

Use `Fyre\Security\Cors` and `Fyre\Security\Middleware\CorsMiddleware` when browser code
from another origin needs to access selected application responses.

CORS controls whether browsers expose cross-origin responses to client-side code. It does not
replace authentication, authorization, or CSRF protection.

## Table of Contents

- [Start here](#start-here)
- [Options](#options)
- [Middleware integration](#middleware-integration)
  - [Preflight requests](#preflight-requests)
  - [Skipping requests](#skipping-requests)
- [Applying headers directly](#applying-headers-directly)
- [Related](#related)

## Start here

Add `CorsMiddleware` near the start of the application middleware queue so it can answer
preflight requests before authentication, CSRF protection, and routing:

```php
use Fyre\Http\MiddlewareQueue;
use Fyre\Security\Middleware\CorsMiddleware;

public function middleware(MiddlewareQueue $queue): MiddlewareQueue
{
    return $queue
        ->add(new CorsMiddleware($this, [
            'allowedOrigins' => ['https://app.example.com'],
            'allowedMethods' => ['GET', 'POST', 'PATCH'],
            'allowedHeaders' => ['Content-Type', 'Authorization'],
            'exposedHeaders' => ['X-Request-Id'],
            'allowCredentials' => true,
            'maxAge' => 600,
        ]))
        ->add('error')
        ->add('session')
        ->add('csrf')
        ->add('auth')
        ->add('router');
}
```

Placing `cors` outside `error` also applies allowed CORS headers to responses produced by the
error middleware.

`Engine` maps the `cors` alias by default. The default policy has no allowed origins, so it is
disabled until options are supplied. To configure the alias instead of constructing the
middleware directly, remap it with build arguments:

```php
use Fyre\Http\MiddlewareRegistry;
use Fyre\Security\Middleware\CorsMiddleware;

$registry = app(MiddlewareRegistry::class);
$registry->map('cors', CorsMiddleware::class, [
    'options' => [
        'allowedOrigins' => ['https://app.example.com'],
    ],
]);
```

## Options

- `allowCredentials` (`bool`): emit `Access-Control-Allow-Credentials: true` (default:
  `false`).
- `allowedHeaders` (`string[]`): request headers accepted during preflight (default: `[]`). Use
  `*` to echo the requested headers.
- `allowedMethods` (`string[]`): methods accepted during preflight (default: `GET`, `HEAD`,
  `POST`, `PUT`, `PATCH`, and `DELETE`). Use `*` to echo the requested method.
- `allowedOrigins` (`string[]`): origins allowed to read responses (default: `[]`, which
  disables CORS). Use `*` for public, non-credentialed access.
- `exposedHeaders` (`string[]`): response headers exposed to browser code (default: `[]`).
- `maxAge` (`int|null`): preflight cache lifetime in seconds (default: `null`, which omits the
  header).
- `skipCheck` (`Closure|null`): callback invoked through the container; return `true` to bypass
  all CORS handling for the request.

Origin matching is exact. When credentials are enabled, a wildcard allowed origin is emitted as
the requesting origin rather than `*`, because browsers do not permit wildcard origins on
credentialed responses.

When a specific origin is emitted, responses include `Vary: Origin`. Preflight responses also
vary on `Access-Control-Request-Method` and `Access-Control-Request-Headers`.

## Middleware integration

For an allowed non-preflight request, `CorsMiddleware` invokes the remaining queue and applies
CORS headers to its response. A request from a disallowed origin still reaches the application,
but its response does not contain `Access-Control-Allow-Origin`, so browsers do not expose that
response to the requesting script.

Do not rely on CORS to prevent state changes. Protect cookie-authenticated unsafe requests with
CSRF middleware and enforce authentication and authorization independently.

### Preflight requests

A request is treated as a preflight when it uses `OPTIONS` and includes
`Access-Control-Request-Method`. The middleware returns an empty `204 No Content` response
without invoking the remaining queue.

Allowed preflights receive the configured allow-origin, allow-methods, allow-headers,
credentials, and maximum-age headers. Rejected preflights receive the `Vary` headers but no CORS
allow headers.

Prefer application-level CORS middleware over route middleware. A preflight uses `OPTIONS`, so
it may not match the route for the requested `POST`, `PUT`, `PATCH`, or `DELETE` method before
route middleware can run.

### Skipping requests

Use `skipCheck` to restrict a globally registered middleware to selected paths or request
characteristics:

```php
use Psr\Http\Message\ServerRequestInterface;

$options = [
    'allowedOrigins' => ['https://app.example.com'],
    'skipCheck' => static fn(ServerRequestInterface $request): bool =>
        !str_starts_with($request->getUri()->getPath(), '/api/'),
];
```

Skipped requests continue through the queue without CORS headers. Keep the callback path-based
when the middleware runs before routing because route attributes are not available yet.

## Applying headers directly

`Cors` contains the policy checks and header generation independently of PSR-15 middleware:

```php
use Fyre\Security\Cors;

$cors = new Cors($container, [
    'allowedOrigins' => ['https://app.example.com'],
]);

$response = $cors->addHeaders($request, $response);
```

Use `addHeadersPreflight()` for a preflight response. `canHandleRequest()` and
`isPreflightRequest()` expose the request classification used by the middleware.

## Related

- [HTTP Middleware](../http/middleware.md) - configure the application middleware queue
- [CSRF](csrf.md) - protect cookie-authenticated state-changing requests
- [Content Security Policy (CSP)](csp.md) - restrict resources loaded by rendered pages
- [Rate Limiting](rate-limiting.md) - throttle requests by client, route, or user
