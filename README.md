# FyreFramework

[![CI](https://github.com/elusivecodes/FyreFramework/actions/workflows/ci.yml/badge.svg)](https://github.com/elusivecodes/FyreFramework/actions/workflows/ci.yml)
[![Codecov](https://codecov.io/github/elusivecodes/FyreFramework/branch/main/graph/badge.svg)](https://app.codecov.io/github/elusivecodes/FyreFramework)
[![Packagist Version](https://img.shields.io/packagist/v/fyre/framework.svg)](https://packagist.org/packages/fyre/framework)
[![Packagist Downloads](https://img.shields.io/packagist/dt/fyre/framework.svg)](https://packagist.org/packages/fyre/framework)
[![GitHub License](https://img.shields.io/github/license/elusivecodes/FyreFramework.svg)](LICENSE)

FyreFramework is a modular PHP framework package for HTTP apps, CLI tools, data access, views, caching, queues, and more.

This repository contains the `fyre/framework` package. Install it into your application with Composer and use the pieces you need, or build on the default `Engine` runtime when you want the framework's common services wired together.

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [Getting started](#getting-started)
- [Documentation](#documentation)
- [Development](#development)
- [License](#license)

## Overview

Fyre keeps the framework split into focused subsystems, so you can adopt one piece at a time or use them together in a conventional application stack.

Common use cases include:

- Building web applications with PSR-7 requests, responses, middleware, and routing
- Working with SQL databases through the query layer and ORM
- Rendering templates and forms on the server
- Running background work with queues and workers
- Adding shared services such as caching, logging, mail, events, and validation

## Requirements

- PHP >= 8.5
- Required PHP extensions: `intl`, `mbstring`
- For database connections: `ext-pdo` plus the matching PDO driver such as `pdo_mysql`, `pdo_pgsql`, or `pdo_sqlite`

Optional (depending on the parts you use):

- `ext-curl` (HTTP client requests)
- `ext-memcached` (Memcached cache)
- `ext-openssl` (OpenSSL encryption handler)
- `ext-pcntl` (queue workers and async promises)
- `ext-redis` (Redis cache and queue handlers)

Fyre has no third-party runtime dependencies beyond PSR interfaces (`psr/*`).

## Installation

Install the package with Composer:

```bash
composer require fyre/framework
```

## Getting started

A common starting point is to extend `Fyre\Core\Engine`, register the application instance, and add your middleware, routes, and other app services around it.

```php
use Fyre\Core\Engine;
use Fyre\Core\Loader;
use Fyre\Http\MiddlewareQueue;

final class Application extends Engine
{
    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue
            ->add('error')
            ->add('router')
            ->add('bindings');
    }
}

$loader = (new Loader())
    ->loadComposer('vendor/autoload.php')
    ->register();

$app = new Application($loader);
Application::setInstance($app);
```

Your application repository decides the entry points, bootstrap flow, and project layout around this package.

For a fuller walkthrough, start with [Core](docs/core/index.md), then [Engine](docs/core/engine.md).

## Documentation

Start with the [documentation index](docs/index.md), or jump to the area you need:

- **Core services**: [Core](docs/core/index.md) -> [Container](docs/core/container.md) -> [Engine](docs/core/engine.md)
- **HTTP applications**: [HTTP](docs/http/index.md) -> [Routing](docs/routing/index.md)
- **Data and persistence**: [Database](docs/database/index.md) -> [ORM](docs/orm/index.md)
- **Auth and security**: [Auth](docs/auth/index.md) -> [Security](docs/security/index.md)
- **Shared services**: [Events](docs/events/index.md) -> [Logging](docs/logging/index.md) -> [Mail](docs/mail/index.md) -> [Cache](docs/cache/index.md) -> [Queue](docs/queue/index.md)
- **Rendering and forms**: [View](docs/view/index.md) -> [Form](docs/form/index.md)
- **Tooling and tests**: [Console](docs/console/index.md) -> [Testing](docs/testing/index.md) -> [Utilities](docs/utilities/index.md)

## Development

Install dev dependencies and run the main checks:

```bash
composer install
composer cs
composer phpstan
composer phpstan-tests
composer test:core
```

Integration suites are available for services defined in `docker-compose.yml`:

```bash
docker compose up -d mysql
composer test:mysql
```

Available service-backed suites include `mariadb`, `mysql`, `postgres`, `redis`, `memcached`, and `smtp`.

## License

FyreFramework is released under the [MIT License](LICENSE).
