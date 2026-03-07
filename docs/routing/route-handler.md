# Route Handler

`Fyre\Router\RouteHandler` is a PSR-15 request handler that dispatches a request to the matched `Route`. It is commonly used as the fallback handler after global middleware has executed.

## Table of Contents

- [Purpose](#purpose)
- [Route handler in the pipeline](#route-handler-in-the-pipeline)
- [Requirements](#requirements)
- [Execution model](#execution-model)
- [Route middleware](#route-middleware)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `RouteHandler` when you want to:

- dispatch the matched `Route` produced by router middleware
- run route-level middleware after global middleware
- keep a clean “middleware pipeline → fallback handler” mental model

If you’re building a middleware pipeline, `RouteHandler` is commonly used as the fallback handler for the [Request Handler](../http/request-handler.md).

## Route handler in the pipeline

A typical inbound request flow looks like:

1. Global middleware runs via the [Request Handler](../http/request-handler.md).
2. Router middleware matches a route and stores it on the request as the `route` attribute (see [HTTP Middleware](../http/middleware.md)).
3. `RouteHandler` dispatches the request to the matched `Route`.

## Requirements

`RouteHandler` expects the request to already have a matched route stored as the `route` attribute.

## Execution model

At `handle()` time:

- `RouteHandler` reads `$request->getAttribute('route')`.
- If the route has no route-level middleware (`Route::getMiddleware()` returns an empty array), it calls `Route::handle($request)` directly.
- If the route has middleware, it builds a `MiddlewareQueue` from that middleware, appends a final middleware that calls `Route::handle()`, then executes that queue using a nested `RequestHandler`.

## Route middleware

Route middleware is defined on the route object (via route definitions and groups). It runs after global middleware and before the route itself is executed.

When a route has middleware, that middleware runs before `Route::handle()` is invoked.

This is the right place for per-route concerns like authorization, throttling, or request shaping that only applies to a subset of endpoints.

## Behavior notes

A few behaviors are worth keeping in mind:

- `RouteHandler` throws `Fyre\Router\Exceptions\RouterException` when the `route` request attribute is missing.

## Related

- [Router](router.md)
- [Request Handler](../http/request-handler.md)
- [HTTP Middleware](../http/middleware.md)
- [Route Bindings](route-bindings.md)
