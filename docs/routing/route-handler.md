# Route Handler

Use `Fyre\Router\RouteHandler` to dispatch the route that router middleware already matched.

Most applications use it as the final handler after global middleware has run.

## Table of Contents

- [Start here](#start-here)
- [Requirements](#requirements)
- [Dispatching the route](#dispatching-the-route)
- [Route middleware](#route-middleware)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use `RouteHandler` when you want to:

- dispatch the matched route produced by router middleware
- run route-level middleware before the route destination
- keep route dispatch separate from your global middleware setup

If you are building a middleware flow manually, use `RouteHandler` as the [Request Handler](../http/request-handler.md) fallback so it runs when the global middleware queue is exhausted.

## Requirements

`RouteHandler` expects the request to already have a matched route stored as the `route` attribute.

## Dispatching the route

When `handle()` runs:

- if the route has no route middleware, it dispatches the route directly
- if the route has route middleware, that middleware runs first and the route destination runs last

## Route middleware

Route middleware is defined on the route object (via route definitions and groups). It runs after global middleware and before the route itself is executed.

When a route has middleware, that middleware runs before `Route::handle()` is invoked.

This is the right place for per-route concerns like authorization, throttling, or request shaping that only applies to a subset of endpoints.

## Behavior notes

A few behaviors are worth keeping in mind:

- `RouteHandler` throws `Fyre\Router\Exceptions\RouterException` when the `route` request attribute is missing.
- Route middleware only runs for the matched route. Use global middleware for concerns that should apply to every request.

## Related

- [Router](router.md)
- [Request Handler](../http/request-handler.md)
- [HTTP Middleware](../http/middleware.md)
- [Route Bindings](route-bindings.md)
