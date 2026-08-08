# Routing

Routing covers route definition, request matching, route dispatch, URL generation, route bindings, and route discovery.

## Table of Contents

- [Start here](#start-here)
- [Routing overview](#routing-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Defining routes**: start with [Router](router.md)
- **Dispatching a matched route**: see [Route Handler](route-handler.md)
- **Generating links from aliases**: see [URL Generation](url-generation.md)
- **Resolving route parameters into typed or application-specific values**: see [Route Bindings](route-bindings.md)
- **Using controller attributes and conventions**: see [Route Discovery](route-discovery.md)

## Routing overview

Most applications use routing in three ways:

- define paths, placeholders, route groups, and aliases
- dispatch the matched route after routing has completed
- generate stable links from route aliases instead of hard-coded paths

The main pieces are straightforward:

- `Router` defines routes, matches requests, and generates URLs
- `RouteHandler` dispatches the matched route and any route middleware
- route bindings replace raw route values with custom values, ORM entities, or enum cases
- route discovery builds routes from controller attributes and conventions

## Pages in this section

- [Router](router.md) - define routes, groups, placeholders, and aliases
- [Route Handler](route-handler.md) - dispatch a matched route and run route middleware
- [URL Generation](url-generation.md) - generate paths and full URLs from aliases
- [Route Bindings](route-bindings.md) - resolve route values with callbacks or automatic entity and enum binding
- [Route Discovery](route-discovery.md) - build routes from controller attributes and conventions

## Related

- [HTTP Middleware](../http/middleware.md) - add router and bindings middleware
- [HTTP Requests](../http/requests.md) - read the matched route and route arguments from the request
