# Routing

Routing covers route definition, request matching, route dispatch, URL generation, route bindings, and route discovery.

## Table of Contents

- [Routing flow](#routing-flow)
- [Routing overview](#routing-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Routing flow

Define paths, methods, placeholders, aliases, and groups with [Router](router.md). Router middleware matches the incoming request, then [Route Handler](route-handler.md) runs route middleware and invokes the selected destination.

Use [URL Generation](url-generation.md) to build links from route aliases, [Route Bindings](route-bindings.md) to replace placeholder values before dispatch, and [Route Discovery](route-discovery.md) to register controller routes from attributes and conventions.

## Routing overview

Routing separates registration and matching from dispatch. `Router` owns the route collection, request matching, and URL generation; `RouteHandler` consumes the matched route stored on the request. Bindings and discovery are optional layers around those two responsibilities.

## Pages in this section

- [Router](router.md) - define routes, groups, placeholders, and aliases
- [Route Handler](route-handler.md) - dispatch a matched route and run route middleware
- [URL Generation](url-generation.md) - generate paths and full URLs from aliases
- [Route Bindings](route-bindings.md) - resolve route values with callbacks or automatic entity and enum binding
- [Route Discovery](route-discovery.md) - build routes from controller attributes and conventions

## Related

- [HTTP Middleware](../http/middleware.md) - add router and bindings middleware
- [HTTP Requests](../http/requests.md) - read the matched route and route arguments from the request
