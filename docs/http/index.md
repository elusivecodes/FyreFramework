# HTTP

HTTP covers incoming requests, outgoing responses, middleware, sessions, and the outbound HTTP client.

## Table of Contents

- [Start here](#start-here)
- [HTTP overview](#http-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick the path that matches what you are doing:

- **Handling incoming requests**: [HTTP Requests](requests.md) -> [HTTP Responses](responses.md) -> [HTTP Middleware](middleware.md)
- **Running request flow through middleware**: [HTTP Middleware](middleware.md) -> [Request Handler](request-handler.md)
- **Working with sessions**: [Sessions](sessions.md)
- **Calling external services**: [HTTP Client](client.md)
- **Working with URLs and user agents**: [URI](uri.md) and [User Agents](user-agents.md)

## HTTP overview

Most applications use the HTTP section in two ways:

- **Inbound HTTP**: read a request, pass it through middleware, and return a response
- **Outbound HTTP**: send requests to other services with the HTTP client

The core pieces are straightforward:

- `ServerRequest` gives you request data, headers, uploads, locale negotiation, and request attributes
- `ClientResponse` and its subclasses help you return HTML, JSON, redirects, downloads, and cookies
- `MiddlewareQueue` and `RequestHandler` run shared request logic in order
- `Session` manages per-user state across requests
- `Client` makes outbound HTTP calls and returns client responses

## Pages in this section

- [HTTP Requests](requests.md) - read request data, uploaded files, context, and attributes
- [HTTP Responses](responses.md) - return HTML, JSON, redirects, downloads, and cookies
- [HTTP Middleware](middleware.md) - define the middleware queue and register aliases or groups
- [Request Handler](request-handler.md) - execute a middleware queue and hand off to a fallback handler
- [HTTP Client](client.md) - make outbound HTTP requests and work with client responses
- [Sessions](sessions.md) - store state across requests and configure session handlers
- [URI](uri.md) - read and update URI parts, query parameters, and path segments
- [User Agents](user-agents.md) - identify browsers, platforms, bots, and mobile devices
