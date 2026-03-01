# FyreFramework Documentation

🧭 FyreFramework is a modular framework built from focused subsystems that are designed to compose cleanly.

## Table of Contents

- [Start here](#start-here)
- [Documentation overview](#documentation-overview)
  - [How the Docs Are Organized](#how-the-docs-are-organized)
  - [Mental Model](#mental-model)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re building:

- **HTTP app**: [Core](core/index.md) → [HTTP](http/index.md) → [Routing](routing/index.md) (then [View](view/index.md) if you render HTML)
- **API**: [HTTP](http/index.md) → [Routing](routing/index.md) → [Auth](auth/index.md) / [Security](security/index.md) → [Logging](logging/index.md)
- **Data/persistence**: [Database](database/index.md) → [ORM](orm/index.md)

Common next stops (depending on what you’re building):

- CLI commands and tooling: [Console](console/index.md)
- Templates, rendering, and view helpers: [View](view/index.md)
- Server-side schemas and validation: [Form](form/index.md)
- Logging and log handlers: [Logging](logging/index.md)
- Test utilities and fixtures: [Testing](testing/index.md)
- Background work and workers: [Queue](queue/index.md)
- Caching adapters: [Cache](cache/index.md)
- Security primitives (CSP, CSRF, encryption): [Security](security/index.md)
- General-purpose building blocks: [Utilities](utilities/index.md)

## Documentation overview

🧩 This section gives you a subsystem-first map of the framework and explains how the docs are organized so you can find the APIs you need quickly.

### How the Docs Are Organized

This documentation is organized by subsystem. Each section starts with an index page and then drills into the public APIs you’ll use in application code.

### Mental Model

FyreFramework’s subsystems are designed to compose cleanly: you wire services into the container, then build behavior by layering features (middleware, routing, persistence, and utilities) on top.

If you want a simple mental model to carry through the docs:

- **Start with the container and defaults.** [Core](core/index.md) explains the container-centric model used across the framework, and [Engine](core/engine.md) shows the “ready-to-use” baseline that pre-registers common bindings.
- **Think in request flow when building HTTP apps.** Requests pass through [HTTP Middleware](http/middleware.md), then routing selects a handler, and the handler produces a response. Start with [HTTP](http/index.md), then move to [Routing](routing/index.md) and the [Request handler](http/request-handler.md).
- **Treat persistence as two layers.** The database layer handles connections and queries, and the ORM builds a record/relationship layer on top of database access. Start with [Database](database/index.md), then move to [ORM](orm/index.md).

After that, add the shared subsystems you need (events, caching, logging, queues, forms/validation, security, and so on) and keep the container as the integration point.

## Pages in this section

- [Core](core/index.md) — the container-centric runtime: service wiring, config, language, helpers, macros, and debugging.
- [HTTP](http/index.md) — requests/responses, middleware execution, sessions, and the HTTP client for outbound calls.
- [Routing](routing/index.md) — route definition and matching, discovery, bindings, and URL generation.
- [View](view/index.md) — rendering templates with layouts using elements, cells, and helpers.
- [Form](form/index.md) — server-side schemas and validators for parsing input, validating fields, and processing submissions (for template-side form markup, see [View → Forms](view/forms.md)).
- [Auth](auth/index.md) — authentication, current-user resolution, and authorization checks.
- [Security](security/index.md) — CSP/CSRF, encryption, and rate limiting primitives for hardening apps.
- [Database](database/index.md) — connections, query building/execution, schema tools, migrations, and type casting.
- [ORM](orm/index.md) — models, entities, relationships, querying, and persistence workflows.
- [Cache](cache/index.md) — caching values with configurable handlers and consistent key/TTL behavior.
- [Events](events/index.md) — hook points and listeners for observing or altering runtime behavior.
- [Logging](logging/index.md) — structured logging with levels, scope filtering, and configurable handlers.
- [Mail](mail/index.md) — building and sending email through configurable transports.
- [Queue](queue/index.md) — background jobs, message processing, and the worker runtime.
- [Console](console/index.md) — command discovery, argument parsing, and consistent CLI lifecycle events.
- [Testing](testing/index.md) — PHPUnit utilities: base test case, fixtures, and reusable assertions/constraints.
- [Utilities](utilities/index.md) — core utilities and classes for filesystem operations, collections, promises, date/time, colors, and PDF generation.
