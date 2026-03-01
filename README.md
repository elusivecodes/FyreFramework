# FyreFramework

FyreFramework is a modern, modular PHP framework built around small, focused components: HTTP, routing, middleware, dependency injection, events, caching, logging, database access, ORM, views, validation, queues, and more.

## Table of Contents

- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start (Mental Model)](#quick-start-mental-model)
- [Where to start](#where-to-start)

## Introduction

FyreFramework aims to give you a cohesive set of services without locking you into a single monolith. You can adopt the parts you need (or use them together) while keeping boundaries clear between subsystems.

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

📌 Note: Fyre has no third-party runtime dependencies beyond PSR interfaces (`psr/*`).

## Installation

Use Composer:

```bash
composer require fyre/framework
```

## Quick Start (Mental Model)

🧠 Fyre is designed around a small set of concepts that compose:

- **Container (Engine)** wires services and config: [Engine](docs/core/engine.md), [Core](docs/core/index.md)
- **Middleware pipeline** runs cross-cutting concerns: [HTTP Middleware](docs/http/middleware.md)
- **Router** matches a request to a handler: [Router](docs/routing/router.md)
- **Request handler** executes middleware and falls back to routing: [Request Handler](docs/http/request-handler.md)
- **Response** is emitted to the client: [HTTP Responses](docs/http/responses.md)

## Where to start

Pick a path based on what you’re building:

- **Build an HTTP app**: [Core](docs/core/index.md) → [HTTP](docs/http/index.md) → [Routing](docs/routing/index.md)
  - Recommended deep dives: [Engine](docs/core/engine.md), [HTTP Middleware](docs/http/middleware.md), [Router](docs/routing/router.md)
- **Persistence (DB + ORM)**: [Database](docs/database/index.md) → [ORM](docs/orm/index.md)
- **Templates and view helpers**: [View](docs/view/index.md)
- **CLI and background work**: [Console](docs/console/index.md) and [Queue](docs/queue/index.md)

Or start at the full documentation index:

- [Documentation](docs/index.md)

More sections:

- [Utilities](docs/utilities/index.md)
- [Logging](docs/logging/index.md)
- [Testing](docs/testing/index.md)
