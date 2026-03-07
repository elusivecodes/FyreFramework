# FyreFramework

FyreFramework is a modern, modular PHP framework built around small, focused components: HTTP, routing, middleware, dependency injection, events, caching, logging, database access, ORM, views, validation, queues, and more.

## Table of Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Where to start](#where-to-start)

## Introduction

FyreFramework aims to give you a cohesive set of services without locking you into a single monolith. You can adopt the parts you need (or use them together) while keeping boundaries clear between subsystems.

It is built for projects that want framework-level cohesion without framework-level lock-in. If you use the default runtime, that cohesion is provided through the container-centric `Engine`, which pre-registers the framework’s common services and namespaces.

Key characteristics:

- Modular by default, so you can adopt individual subsystems or run the full stack together
- PSR-aligned HTTP, container, logging, and event abstractions that fit cleanly into modern PHP applications
- Strong built-in tooling for common application concerns including ORM, validation, queues, mail, caching, and testing

Common use cases include:

- Building web applications with PSR-7 requests/responses and middleware
- Structuring request routing and controller-style handling
- Integrating persistence via the database layer and ORM
- Adding background work via queues and workers
- Using first-class utilities (caching, template rendering, HTML/form helpers, etc.)

## Requirements

- PHP >= 8.5
- PHP extensions: `intl`, `mbstring`

Optional (depending on the parts you use):

- `ext-curl` (HTTP client requests)
- `ext-memcached` (Memcached cache)
- `ext-openssl` (OpenSSL encryption handler)
- `ext-pcntl` (queue workers and async promises)
- `ext-pdo` (database connections)
- `ext-redis` (Redis cache and queue handlers)

Fyre has no third-party runtime dependencies beyond PSR interfaces (`psr/*`).

## Installation

Use Composer:

```bash
composer require fyre/framework
```

## Where to start

Start with the [documentation index](docs/index.md), or pick a path based on what you’re building:

- **HTTP applications**: [Core](docs/core/index.md) → [HTTP](docs/http/index.md) → [Routing](docs/routing/index.md)
- **Data/persistence**: [Database](docs/database/index.md) → [ORM](docs/orm/index.md)

Common next stops:

- Templates, rendering, and view helpers: [View](docs/view/index.md)
- Server-side schemas and validation: [Form](docs/form/index.md)
- CLI commands and tooling: [Console](docs/console/index.md)
- Background work and workers: [Queue](docs/queue/index.md)
- Caching adapters: [Cache](docs/cache/index.md)
- Logging and log handlers: [Logging](docs/logging/index.md)
- Security primitives: [Security](docs/security/index.md)
- Test utilities and fixtures: [Testing](docs/testing/index.md)
- General-purpose building blocks: [Utilities](docs/utilities/index.md)
