# Rate Limiting

`Fyre\Security\Middleware\RateLimiterMiddleware` enforces request rate limits in an HTTP middleware pipeline.

Rate limiting protects endpoints from excessive traffic by tracking request “usage” over time and rejecting requests that exceed a configured budget. In Fyre, rate limiting is implemented as HTTP middleware backed by cache, with flexible identifiers (who is being limited) and request cost (how expensive a request is).

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
  - [Built-in strategies](#built-in-strategies)
- [Identifiers](#identifiers)
  - [Supported identifier sources](#supported-identifier-sources)
- [Limits and cost](#limits-and-cost)
  - [Skipping checks](#skipping-checks)
- [Response headers](#response-headers)
- [Middleware integration](#middleware-integration)
  - [Registering a shared rate limiter middleware](#registering-a-shared-rate-limiter-middleware)
  - [Overriding limit, window, and cost inline](#overriding-limit-window-and-cost-inline)
- [Method guide](#method-guide)
  - [`RateLimiterMiddleware`](#ratelimitermiddleware)
  - [`RateLimiter`](#ratelimiter)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Rate limiting is a practical boundary for:

- public endpoints (login, password reset, token issuance)
- expensive operations (search, file generation, report endpoints)
- burst smoothing on APIs consumed by automated clients

Unlike a simple “requests per minute” counter, Fyre’s rate limiting can account for request cost so that a single expensive request can consume more of the budget than a cheap one.

## Mental model

🧠 Rate limiting is a short pipeline of responsibilities:

- `RateLimiterMiddleware` decides whether a request should be checked, checks the limit, and either continues or throws.
- A `RateLimiter` strategy stores and reads the per-identifier state from cache, then returns a rate limit result.
- On success, the middleware adds standard rate limit headers to the response.
- On rejection, the middleware throws `TooManyRequestsException` with a `Retry-After` header based on the limiter’s reset time.

### Built-in strategies

`RateLimiterMiddleware` can select a built-in strategy using the `strategy` option:

- `slidingWindow` (default) — `SlidingWindowRateLimiter`
- `fixedWindow` — `FixedWindowRateLimiter`
- `tokenBucket` — `TokenBucketRateLimiter`

You can also provide a custom limiter class via the `className` option (it must extend `RateLimiter`).

## Identifiers

An identifier is the “key space” used to track usage. It is configured on the limiter via the `identifier` option, which can be either:

- a list of identifier sources (strings)
- a callback that returns a string identifier for the request

When `identifier` is a list, the identifier is assembled by concatenating these sources (with `_`) in the order provided.

📌 Note: If your app runs behind a reverse proxy, be careful with IP-based identification. The built-in `ip` identifier uses `HTTP_X_FORWARDED_FOR` when present, and that value is not parsed or normalized. Prefer `route`/`user`, or use a callback that applies your proxy trust rules.

### Supported identifier sources

The base `RateLimiter` supports three identifier source strings:

- `ip` — uses the request server params (`HTTP_X_FORWARDED_FOR` when present, otherwise `REMOTE_ADDR`)
- `route` — uses `Controller::action` when the request has a `route` attribute that is a `ControllerRoute`, and always includes the client IP
- `user` — uses `user_{id}` when the request has a `user` attribute with an `id` property, otherwise falls back to the client IP

Internally, the assembled identifier is converted into a cache key; the raw identifier is not used as the cache key directly.

## Limits and cost

The limiter is configured with three core values:

- `limit` — maximum budget within the window (default: `60`)
- `window` — time window in seconds (default: `60`)
- `cost` — budget cost of the request (default: `1`)

Cost can be configured as either a fixed integer or a callback. When it’s a callback, the `RateLimiter` computes cost by calling it through the container with the current request.

### Skipping checks

You can bypass rate limiting for specific requests using the `skipCheck` option. When provided, `RateLimiter::shouldSkip()` calls it through the container and skips the limiter when it returns `true`.

## Response headers

When rate limit data is available, responses include:

- `X-RateLimit-Limit` — the effective limit
- `X-RateLimit-Remaining` — remaining budget after the request
- `X-RateLimit-Reset` — the reset time as a UNIX timestamp

When a request is rejected, `RateLimiterMiddleware` throws `TooManyRequestsException` with:

- `Retry-After` — seconds until the reset time (minimum `1`)

## Middleware integration

📌 Rate limiting is applied like any other middleware. Register a middleware alias and add it to a `MiddlewareQueue` (see [HTTP Middleware](../http/middleware.md) for the full pipeline model).

### Registering a shared rate limiter middleware

```php
use Fyre\Core\Container;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Security\Middleware\RateLimiterMiddleware;
use Psr\Http\Message\ServerRequestInterface;

$container = Container::getInstance();
$registry = new MiddlewareRegistry($container);
$container->instance(MiddlewareRegistry::class, $registry);

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

## Method guide

### `RateLimiterMiddleware`

#### **Run rate limiting in a middleware pipeline** (`process()`)

Checks the request against the configured limiter and either continues to the next handler or throws `TooManyRequestsException`.

Arguments:
- `$request` (`ServerRequestInterface`): the incoming request.
- `$handler` (`RequestHandlerInterface`): the next handler in the chain.
- `$limit` (`string|null`): optional limit override (cast to `int` when truthy).
- `$window` (`string|null`): optional window override in seconds (cast to `int` when truthy).
- `$cost` (`string|null`): optional cost override (cast to `int` when truthy).

```php
$response = $middleware->process($request, $handler);

// Optional inline overrides (limit, window, cost):
$response = $middleware->process($request, $handler, '30', '60', '1');
```

### `RateLimiter`

#### **Check a request against a limiter** (`checkLimit()`)

Implemented by each limiter strategy to track request usage and return rate limit data.

Arguments:
- `$request` (`ServerRequestInterface`): the incoming request.
- `$limit` (`int|null`): optional request limit override.
- `$window` (`int|null`): optional time window override in seconds.
- `$cost` (`int|null`): optional request cost override.

```php
$data = $limiter->checkLimit($request);
```

#### **Add rate limit headers to a response** (`addHeaders()`)

Adds `X-RateLimit-*` headers when rate limit data is available.

Arguments:
- `$response` (`ResponseInterface`): the response to add headers to.
- `$data` (`array`): the rate limit data.

```php
$response = $limiter->addHeaders($response, $data);
```

#### **Decide whether to skip rate limiting** (`shouldSkip()`)

Calls the configured `skipCheck` callback (if any) to bypass rate limiting for specific requests.

Arguments:
- `$request` (`ServerRequestInterface`): the incoming request.

```php
if ($limiter->shouldSkip($request)) {
    return $handler->handle($request);
}
```

#### **Get the rejection message** (`getMessage()`)

Returns the configured rate limit message used when throwing `TooManyRequestsException`.

```php
$message = $limiter->getMessage();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Inline middleware arguments are strings; `RateLimiterMiddleware` only applies an override when the value is truthy (so `'0'` is treated as “no override”), and otherwise casts with `(int)`.
- The built-in strategies assume `limit` and `window` are positive integers; non-numeric values (cast to `0`) or explicit `0` configured via options can lead to runtime errors or misleading results.
- The `route` identifier always includes the client IP; it does not group all clients together for the same controller action.
- The `ip` identifier uses the raw `HTTP_X_FORWARDED_FOR` string when present (it is not parsed or normalized).
- If the configured cache does not include the `ratelimiter` config key, `RateLimiter` registers one automatically using `FileCacher` with a `ratelimiter:` prefix.
- Rate limiting relies on cache persistence; when `CacheManager` is disabled (by default when `App.debug` is enabled), it uses a no-op cache handler and will not throttle across requests.

## Related

- [HTTP Middleware](../http/middleware.md) — how to register middleware and pass inline arguments.
- [Cache](../cache/index.md) — how cache persistence affects rate limiting behavior.
- [Routing](../routing/index.md) — how route matching influences the `route` identifier source.
