# HTTP

HTTP covers request/response messages, middleware execution, session integration, and making outbound HTTP calls.

## Table of Contents

- [Start here](#start-here)
- [HTTP overview](#http-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re doing:

- **Handling inbound requests**: start with [HTTP Requests](requests.md) and [HTTP Responses](responses.md), then learn the [HTTP Middleware](middleware.md) pipeline and the [Request Handler](request-handler.md).
- **Calling external services**: start with the [HTTP Client](client.md) (then see [HTTP Client](client.md#handlers) for transport handlers and [HTTP Client](client.md#testing) for mocks).
- **Working with sessions**: see [Sessions](sessions.md) (typically initialized via middleware).

## HTTP overview

This section documents the framework’s HTTP primitives (requests, responses, middleware, sessions, and URL utilities) and how they fit together, using PSR-7 messages, PSR-15 middleware, and a PSR-18 HTTP client.

In practice, the HTTP layer is a small set of collaborating pieces:

- Requests and responses are PSR-7 message objects your code reads from and returns.
- Middleware (PSR-15) composes request handling into an ordered pipeline, ending in a response.
- Sessions are attached to requests and typically managed by middleware during the pipeline.
- The HTTP client (PSR-18) provides outbound requests, response handling, and test-friendly hooks.
- URI and user agent utilities cover common parsing and inspection tasks.

If you want a simple mental model:

- **Inbound flow**: `ServerRequest` → middleware queue → router/handler → `Response` → emitter
- **Outbound flow**: `Client` → handler (cURL/mock/custom) → `Client\Response`

Note: Inbound request handlers typically return `Fyre\Http\ClientResponse` (a server-friendly PSR-7 response). Outbound calls return `Fyre\Http\Client\Response` (the client response wrapper).

If you’re working on inbound HTTP handling, start with requests/responses and the middleware pipeline. For outbound calls, start with the HTTP client.

## Pages in this section

- [HTTP Requests](requests.md) — reading input, uploaded files, attributes, and request utilities.
- [HTTP Responses](responses.md) — response types, headers/cookies, and emitting responses.
- [HTTP Middleware](middleware.md) — middleware pipeline model, queue, and registry.
- [Request Handler](request-handler.md) — how middleware is executed and how fallbacks work.
- [HTTP Client](client.md) — making outbound requests, handlers, and testing.
- [Sessions](sessions.md) — session lifecycle and request integration.
- [URI](uri.md) — parsing and manipulating URIs, queries, and paths.
- [User Agents](user-agents.md) — parsing and identifying browsers, platforms, and bots.
