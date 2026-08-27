# FyreFramework Documentation

Use this page to find the part of the `fyre/framework` package you need.

These docs cover the framework APIs your application uses. Project layout, entry points, and application-specific bootstrap remain in your application repository.

## Table of Contents

- [Start here](#start-here)
- [Framework overview](#framework-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you are building:

- **New applications**: [Getting Started](getting-started.md)
- **Core services**: [Core](core/index.md) -> [Engine](core/engine.md) -> [Container](core/container.md)
- **HTTP applications**: [HTTP](http/index.md) -> [Routing](routing/index.md)
- **Data and persistence**: [Database](database/index.md) -> [ORM](orm/index.md)
- **Auth and security**: [Auth](auth/index.md), [Security](security/index.md)
- **Shared services**: [Events](events/index.md), [Logging](logging/index.md), [Mail](mail/index.md), [Cache](cache/index.md), [Queue](queue/index.md)
- **Rendering and forms**: [View](view/index.md), [Form](form/index.md)
- **Tooling and tests**: [Console](console/index.md), [Testing](testing/index.md), [Utilities](utilities/index.md)

## Framework overview

Most applications touch a few main areas:

- **Core**: container, configuration, language, helpers, and the default `Engine`
- **HTTP and routing**: requests, responses, middleware, sessions, and route handling
- **Database and ORM**: connections, queries, models, entities, and relationships
- **Shared services**: auth, security, cache, events, logging, mail, and queue processing
- **Presentation and forms**: templates, helpers, forms, and validation

Start with the section you need most, then follow the related links from there.

## Pages in this section

- [Getting Started](getting-started.md) - requirements, installation, application bootstrap, and next steps
- [Core](core/index.md) - configuration, language, helpers, the container, and the default engine
- [HTTP](http/index.md) - requests, responses, middleware, sessions, and outbound HTTP calls
- [Routing](routing/index.md) - route definition, matching, bindings, and URL generation
- [View](view/index.md) - templates, layouts, elements, cells, and view helpers
- [Form](form/index.md) - server-side forms, schemas, validation, and submission handling
- [Auth](auth/index.md) - authentication, authorization, and auth middleware
- [Security](security/index.md) - CSP, CSRF, encryption, and rate limiting
- [Database](database/index.md) - connections, queries, schema tools, migrations, and types
- [ORM](orm/index.md) - models, entities, relationships, queries, and persistence
- [Cache](cache/index.md) - cache handlers, common operations, locks, and tagged entries
- [Events](events/index.md) - events and listeners
- [Logging](logging/index.md) - loggers, levels, scopes, and handlers
- [Mail](mail/index.md) - building and sending email
- [Queue](queue/index.md) - jobs, queues, and worker processing
- [Console](console/index.md) - commands, prompts, and CLI tooling
- [Testing](testing/index.md) - PHPUnit helpers, fixtures, and framework test utilities
- [Utilities](utilities/index.md) - general-purpose helpers such as files, images, colors, collections, promises, and date/time tools
- [Contributing](contributing/index.md) - repository checks, test suites, and documentation conventions
