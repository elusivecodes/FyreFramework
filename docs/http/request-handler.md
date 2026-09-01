# Request Handler

Use `Fyre\Http\RequestHandler` when you need to run a middleware queue manually in a custom entry point or a middleware test.

Most applications will not instantiate it directly.

## Table of Contents

- [Start here](#start-here)
- [What the request handler does](#what-the-request-handler-does)
- [Fallback handler](#fallback-handler)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

A common custom entry point pattern is:

```php
use Fyre\Http\RequestHandler;
use Fyre\Router\RouteHandler;
use Psr\Http\Message\ServerRequestInterface;

$app = app();

$handler = $app->use(RequestHandler::class, [
    'fallbackHandler' => $app->use(RouteHandler::class),
]);

$request = $app->use(ServerRequestInterface::class);
$response = $handler->handle($request);
```

If you already receive a `RequestHandler` or `RequestHandlerInterface` through dependency injection, just call `handle($request)`.

## What the request handler does

For each request it:

1. takes the next queue entry
2. resolves aliases and groups through `MiddlewareRegistry`
3. calls the middleware
4. continues to the next queue item when that middleware calls `$handler->handle($request)`

For middleware authoring and queue definitions, see [HTTP Middleware](middleware.md).

## Fallback handler

When the queue runs out, `RequestHandler`:

- calls `fallbackHandler->handle($request)` when a fallback handler was provided, or
- returns a `204 No Content` `ClientResponse` when there is no fallback handler

A common pattern is to use routing as the fallback handler so routes only run after global middleware completes.

If you use `RouteHandler` as the fallback handler, router middleware must already have matched a route and stored it on the request.

## Behavior notes

- `RequestHandler` advances the underlying queue as it runs, so rewind the queue or use a fresh one before handling another request with the same instance.
- Middleware groups run as nested queues and return control to the outer queue when they finish.
- Each Fyre `ServerRequest` passed to `handle()` replaces the current scoped request instance in the container. This means a request modified by middleware is available to downstream code that resolves `ServerRequest` or `ServerRequestInterface` from the container.
- The existing scoped request binding is preserved. After the scope is cleared, the next resolution creates a fresh request through `ServerRequestFactory`.

## Related

- [HTTP Middleware](middleware.md)
- [HTTP Requests](requests.md)
- [HTTP Responses](responses.md)
- [Router](../routing/router.md)
