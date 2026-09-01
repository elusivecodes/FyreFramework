# HTTP

HTTP covers incoming requests, outgoing responses, cookies, middleware, sessions, and the outbound HTTP client.

## Table of Contents

- [Request flow](#request-flow)
- [HTTP overview](#http-overview)
- [Pages in this section](#pages-in-this-section)

## Request flow

For an incoming request, start with [HTTP Requests](requests.md), build the application queue with [HTTP Middleware](middleware.md), and execute it through [Request Handler](request-handler.md). Routing middleware matches the request, and the route handler invokes the destination. The application then returns an [HTTP Response](responses.md).

[Sessions](sessions.md), [Cookies](cookies.md), and [User Agents](user-agents.md) add request-specific state or metadata around that flow. They can be read independently when that is the only feature you need.

## HTTP overview

Inbound HTTP uses `ServerRequest`, middleware, routing, and `ClientResponse`. Outbound calls use [HTTP Client](client.md), while [HTTP Factories](factories.md) create implementation-independent PSR-7 objects through the PSR-17 contracts. [URI](uri.md) documents the URI value object shared by both sides.

## Pages in this section

- [HTTP Requests](requests.md) - read request data, uploaded files, context, and attributes
- [HTTP Responses](responses.md) - return HTML, JSON, redirects, downloads, and cookies
- [HTTP Middleware](middleware.md) - define the middleware queue and register aliases or groups
- [Request Handler](request-handler.md) - execute a middleware queue and hand off to a fallback handler
- [HTTP Client](client.md) - make outbound HTTP requests and work with client responses
- [HTTP Factories](factories.md) - create PSR-7 HTTP objects through PSR-17 interfaces
- [Cookies](cookies.md) - create, parse, scope, and store HTTP cookies
- [Sessions](sessions.md) - store state across requests and configure session handlers
- [URI](uri.md) - read and update URI parts, query parameters, and path segments
- [User Agents](user-agents.md) - identify browsers, platforms, bots, and mobile devices
