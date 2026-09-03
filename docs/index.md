# FyreFramework Documentation

Use this page to find the part of the `fyre/framework` package you need.

These docs cover the framework APIs your application uses. Project layout, entry points, and application-specific bootstrap remain in your application repository.

## Table of Contents

- [Start here](#start-here)
- [Release and support](#release-and-support)
- [Pages in this section](#pages-in-this-section)

## Start here

New applications should begin with [Getting Started](getting-started.md). For an HTTP application, continue through [Core](core/index.md), [HTTP](http/index.md), and [Routing](routing/index.md). Add [Database](database/index.md) and [ORM](orm/index.md), [Auth](auth/index.md) and [Security](security/index.md), or [View](view/index.md) and [Form](form/index.md) when the application needs them.

[Console](console/index.md) covers command-line entry points and framework commands. [Testing](testing/index.md) covers the PHPUnit integration, fixtures, and boundary-specific helpers.

Before sending an application to production, use [Deployment](deployment.md) to review runtime
configuration, storage, migrations, security controls, workers, and health checks.

## Release and support

- [Changelog](../CHANGELOG.md) - user-visible changes, deprecations, and breaking changes
- [Security Policy](../SECURITY.md) - supported releases and private vulnerability reporting
- [API Stability](stability.md) - public API, internal symbols, and compatibility guarantees

## Pages in this section

- [Getting Started](getting-started.md) - requirements, installation, application bootstrap, and next steps
- [Deployment](deployment.md) - prepare configuration, services, migrations, security, workers, and health checks for production
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
