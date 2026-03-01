# Request Handler

`Fyre\Http\RequestHandler` executes middleware from a `MiddlewareQueue` and uses `MiddlewareRegistry` to resolve aliases and groups into executable middleware.

This page focuses on request flow through the queue, fallback handler behavior, and how the container is updated during request handling.

## Table of Contents

- [Purpose](#purpose)
- [Request handler in the pipeline](#request-handler-in-the-pipeline)
- [Execution model](#execution-model)
- [Fallback handler](#fallback-handler)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `RequestHandler` when you have a middleware queue you want to execute, and you want a well-defined “final handler” once the queue is exhausted (often routing).

Most application code won’t instantiate `RequestHandler` directly, but it can be useful when you’re building a custom HTTP runtime or testing a middleware stack.

The example below uses the `app()` helper to resolve services from the engine container (see [Helpers](../core/helpers.md)).

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

If you prefer dependency injection, accept a `RequestHandler` (or `RequestHandlerInterface`) parameter and call `handle()` directly.

If you use `RouteHandler` as the fallback handler, ensure router middleware has already matched a route and stored it on the request (via the `route` attribute). Otherwise, route dispatch will fail.

## Request handler in the pipeline

🧠 The middleware pipeline is built from three pieces:

- `MiddlewareQueue` stores middleware entries in order.
- `MiddlewareRegistry` resolves string entries into executable middleware (aliases, groups, and optional inline arguments).
- `RequestHandler` executes the queue, calling each middleware with the request and a handler for “the next step”.

For middleware authoring and registry behavior, see [HTTP Middleware](middleware.md).

## Execution model

- Each call to `handle()` resolves the current queue item via `MiddlewareRegistry` and executes it.
- `RequestHandler` resolves the current middleware, advances the queue, then invokes it; a middleware that calls `$handler->handle($request)` continues with the next item.
- Middleware entries can be callables or PSR-15 `MiddlewareInterface` instances (which are invoked via `process()`).
- Callable middleware is invoked as `$middleware($request, $handler)` and is expected to return a `ResponseInterface`.

## Fallback handler

When the middleware queue is exhausted, `RequestHandler`:

- calls `fallbackHandler->handle($request)` if a fallback handler was provided, otherwise
- returns a `ClientResponse` with status code `204 No Content` (see [HTTP Responses](responses.md)).

A common pattern is to use routing as the fallback handler (for example `RouteHandler`) so routes only run after global middleware completes.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `RequestHandler` advances the underlying `MiddlewareQueue` as it runs; if you reuse the same handler/queue instance, call `MiddlewareQueue::rewind()` (or construct a fresh queue) before handling a new request.
- If the incoming request is `ServerRequest`, `RequestHandler` registers it into the container as the current instance of `ServerRequest::class` for downstream resolution (other `ServerRequestInterface` implementations are not registered).
- Middleware is resolved before the queue advances; the handler then advances the queue and invokes the middleware. A middleware that calls `$handler->handle($request)` continues with the next item.
- When a middleware group alias is resolved, it runs in a nested `RequestHandler` and uses the current handler as its fallback (so control returns to the outer queue).
- If you use `RouteHandler` as the fallback handler, it expects a `route` attribute on the request (set by router middleware).

## Related

- [HTTP Requests](requests.md)
- [HTTP Responses](responses.md)
- [HTTP Middleware](middleware.md)
- [Engine](../core/engine.md)
- [Router](../routing/router.md)
- [Security](../security/index.md)
