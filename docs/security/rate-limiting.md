# Rate Limiting

Use `Fyre\Security\Middleware\RateLimiterMiddleware` when you want to throttle requests by client, route, or user.

Rate limiting tracks request usage over time and rejects requests that exceed a configured budget.

## Table of Contents

- [Start here](#start-here)
- [Built-in strategies](#built-in-strategies)
- [Identifiers](#identifiers)
  - [Supported identifier sources](#supported-identifier-sources)
- [Limits and cost](#limits-and-cost)
  - [Skipping checks](#skipping-checks)
  - [Cache persistence](#cache-persistence)
- [Response headers](#response-headers)
- [Middleware integration](#middleware-integration)
  - [Registering a shared rate limiter middleware](#registering-a-shared-rate-limiter-middleware)
  - [Overriding limit, window, and cost inline](#overriding-limit-window-and-cost-inline)
- [Related](#related)

## Start here

Use rate limiting when you want to:

- protect public endpoints such as login, password reset, and token issuance
- limit expensive operations such as search, file generation, and report endpoints
- smooth bursts on APIs consumed by automated clients

Unlike a simple “requests per minute” counter, Fyre’s rate limiting can account for request cost so that a single expensive request can consume more of the budget than a cheap one.

## Built-in strategies

`RateLimiterMiddleware` can select a built-in strategy using the `strategy` option:

- `slidingWindow` (default) - `SlidingWindowRateLimiter` estimates a moving window by weighting the previous fixed bucket and combining it with the current bucket
- `fixedWindow` - `FixedWindowRateLimiter` counts usage in discrete windows and can permit bursts around window boundaries
- `tokenBucket` - `TokenBucketRateLimiter` refills budget steadily over time and allows bursts while sufficient budget remains

You can also provide a custom limiter class via the `className` option (it must extend `RateLimiter`).

## Identifiers

An identifier is the “key space” used to track usage. It is configured on the limiter via the `identifier` option, which can be either:

- a list of identifier sources (strings)
- a callback that returns a string identifier for the request

The default is `['ip']`. When `identifier` is a list, the identifier is assembled by concatenating these sources (with `_`) in the order provided.

If your app runs behind a reverse proxy, be careful with IP-based identification. By default, the built-in `ip` identifier uses `REMOTE_ADDR`. When `App.trustProxy` is enabled with no trusted proxy list, the rightmost `X-Forwarded-For` address is used. A non-empty `App.trustedProxies` list restricts resolution to explicitly trusted proxy hops. For a different forwarded header or custom trust rules, use an `identifier` callback.

### Supported identifier sources

The base `RateLimiter` supports three identifier source strings:

- `ip` — uses `REMOTE_ADDR` by default and resolves the configured forwarded IP chain according to the application proxy policy
- `route` — uses `Controller::action` when the request has a `route` attribute that is a `ControllerRoute`, and always includes the client IP
- `user` — uses `user_{id}` when the request has a `user` attribute with an `id` property, otherwise falls back to the client IP

Place the limiter after routing middleware when using `route`, and after authentication middleware when using `user`, so those request attributes are available.

Unrecognized source names do not contribute to the identifier. Use one of the supported names or provide a callback for custom identification.

## Limits and cost

The limiter is configured with three core values:

- `limit` — maximum budget within the window (default: `60`)
- `window` — time window in seconds (default: `60`)
- `cost` — budget cost of the request (default: `1`)

Proxy trust uses the application configuration shared with `ServerRequest`:

- `App.trustProxy` — whether forwarded IP headers should be considered (default: `false`)
- `App.trustedProxies` — proxy IPs allowed to supply forwarded headers (default: `[]`; an empty list accepts the rightmost forwarded address)

Cost can be configured as either a fixed integer or a callback. When it’s a callback, the `RateLimiter` computes cost by calling it through the container with the current request.

The remaining limiter options are:

- `cacheConfig` (`string`): cache configuration used to store limiter state (default: `ratelimiter`).
- `message` (`string`): exception message used when a request is rejected (default: `Rate limit exceeded`).

`limit` and `window` must be greater than zero, while `cost` can be zero but not negative. Invalid values raise an `InvalidArgumentException` when the request is checked.

### Skipping checks

You can bypass rate limiting for specific requests using the `skipCheck` option (`Closure|null`, default: `null`). When provided, `RateLimiter::shouldSkip()` calls it through the container and skips the limiter only when it returns `true`. Skipped requests are passed directly to the next handler and do not receive rate limit headers.

### Cache persistence

Rate limit state is stored through `CacheManager`. If `cacheConfig` does not exist, the limiter registers a `FileCacher` config with the same name and a matching prefix; the default is `ratelimiter` with the prefix `ratelimiter:`.

Rate limiting requires a persistent cache. When `CacheManager` is disabled—by default while `App.debug` is enabled—it resolves a do-nothing handler and does not throttle across requests.

## Response headers

When rate limit data is available, responses include:

- `X-RateLimit-Limit` - the effective limit
- `X-RateLimit-Remaining` - remaining budget after the request
- `X-RateLimit-Reset` - the strategy-specific reset timestamp

For fixed and sliding windows, `X-RateLimit-Reset` is the next discrete window boundary. For token buckets, it is the estimated time when the bucket will be full again.

When a request is rejected, `RateLimiterMiddleware` throws `TooManyRequestsException` (HTTP 429) with:

- `Retry-After` - seconds until the reported `X-RateLimit-Reset` time (minimum `1`)

## Middleware integration

Rate limiting is applied like any other middleware. Register a middleware alias and add it to a `MiddlewareQueue` (see [HTTP Middleware](../http/middleware.md)).

### Registering a shared rate limiter middleware

```php
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Security\Middleware\RateLimiterMiddleware;
use Psr\Http\Message\ServerRequestInterface;

$registry = app(MiddlewareRegistry::class);

$registry->map('throttle', RateLimiterMiddleware::class, [
    'options' => [
        'strategy' => 'slidingWindow',
        'limit' => 120,
        'window' => 60,
        'identifier' => ['route'],
        'skipCheck' => static fn(ServerRequestInterface $request): bool => $request->getMethod() === 'OPTIONS',
    ],
]);

$queue = new MiddlewareQueue();
$queue->add('throttle');
```

### Overriding limit, window, and cost inline

`RateLimiterMiddleware::process()` accepts optional overrides after the handler: `$limit`, `$window`, and `$cost`. When a middleware entry is referenced as a string, those inline arguments are passed through as strings and then cast to integers by the middleware.

```php
use Fyre\Http\MiddlewareQueue;

$queue = new MiddlewareQueue();

// limit=30, window=60 seconds, cost=1
$queue->add('throttle:30,60,1');
```

Use integer strings for inline overrides. They are cast before the limiter validates the bounds described above.

## Related

- [HTTP Middleware](../http/middleware.md) - register middleware and pass inline arguments
- [Cache](../cache/index.md) - cache persistence affects rate limiting behavior
- [Routing](../routing/index.md) - route matching influences the `route` identifier source
