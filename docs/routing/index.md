# Routing

🧭 Routing maps incoming HTTP requests to handlers by matching the request against a set of route definitions.

## Table of Contents

- [Start here](#start-here)
- [Routing overview](#routing-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- Start with [Router](router.md) to define paths, placeholders, groups, and aliases.
- See [Route Handler](route-handler.md) to dispatch a matched route and run route middleware.
- See [URL Generation](url-generation.md) to generate links from aliases, including query strings and base URI behavior.
- See [Route Bindings](route-bindings.md) to use ORM entities as parameters via binding middleware (including nested bindings).
- See [Route Discovery](route-discovery.md) to generate routes from controller attributes using conventions and caching.

## Routing overview

🧩 At runtime, routing turns a request into a matched route plus a set of extracted arguments, typically before the request reaches the final handler.

In practice, the routing layer covers:

- Defining routes (and route groups), including path placeholders like `{id}` and `{id?}`.
- Matching requests by constraints such as HTTP method and host/scheme/port (when provided).
- Dispatching a matched route (optionally with route-specific middleware).
- Optional features like route discovery and route bindings.

After routing runs, the request typically contains `route` and `routeArguments` attributes (and `relativePath` during matching), which downstream middleware/handlers can use.

## Pages in this section

- [Router](router.md) — defining routes and route groups, matching, and aliases.
- [Route Handler](route-handler.md) — dispatching a matched route and running route middleware.
- [URL Generation](url-generation.md) — generating URLs from route aliases and placeholder values.
- [Route Bindings](route-bindings.md) — substituting matched route arguments with ORM entities.
- [Route Discovery](route-discovery.md) — producing route definitions from controller classes.

## Related

- [HTTP Middleware](../http/middleware.md) — how routing fits into the middleware pipeline.
- [Request Handler](../http/request-handler.md) — how middleware is executed and how fallbacks work.
