# FyreFramework

[![CI](https://github.com/elusivecodes/FyreFramework/actions/workflows/ci.yml/badge.svg)](https://github.com/elusivecodes/FyreFramework/actions/workflows/ci.yml)
[![Codecov](https://codecov.io/github/elusivecodes/FyreFramework/branch/main/graph/badge.svg)](https://app.codecov.io/github/elusivecodes/FyreFramework)
[![Packagist Version](https://img.shields.io/packagist/v/fyre/framework.svg)](https://packagist.org/packages/fyre/framework)
[![Packagist Downloads](https://img.shields.io/packagist/dt/fyre/framework.svg)](https://packagist.org/packages/fyre/framework)
[![GitHub License](https://img.shields.io/github/license/elusivecodes/FyreFramework.svg)](LICENSE)

Use FyreFramework to build HTTP applications and CLI tools with routing, ORM, views, caching, queues, and other focused components.

Install the `fyre/framework` package and use individual subsystems as needed, or extend `Engine` when you want the framework's common application services available by default.

## Table of Contents

- [Start here](#start-here)
- [Requirements](#requirements)
- [Installation](#installation)
- [Application bootstrap](#application-bootstrap)
- [Documentation](#documentation)
- [Repository development](#repository-development)
- [License](#license)

## Start here

- **Install the framework package**: follow [Installation](#installation).
- **Build around the default application services**: see [Application bootstrap](#application-bootstrap).
- **Use a specific subsystem**: browse the [documentation](#documentation).

## Requirements

- PHP >= 8.5
- Required PHP extensions: `ext-intl`, `ext-mbstring`
- For database connections: `ext-pdo` plus the matching PDO driver such as `ext-pdo_mysql`, `ext-pdo_pgsql`, or `ext-pdo_sqlite`

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

## Application bootstrap

A common bootstrap extends `Fyre\Core\Engine`, customizes the middleware queue, and shares the application instance with the framework helpers.

```php
use Fyre\Core\Engine;
use Fyre\Core\Loader;
use Fyre\Http\MiddlewareQueue;

$composer = require 'vendor/autoload.php';

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

$loader = new Loader()
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->register();

$app = new Application($loader);
Application::setInstance($app);
```

Your application repository defines its entry points, routes, configuration paths, and project layout around this package.

Continue with [Engine](docs/core/engine.md), [HTTP Middleware](docs/http/middleware.md), and [Routing](docs/routing/index.md).

## Documentation

Start with the [documentation index](docs/index.md), or jump to the area you need:

- **Core services**: [Core](docs/core/index.md) -> [Engine](docs/core/engine.md) -> [Container](docs/core/container.md)
- **HTTP applications**: [HTTP](docs/http/index.md) -> [Routing](docs/routing/index.md)
- **Data and persistence**: [Database](docs/database/index.md) -> [ORM](docs/orm/index.md)
- **Auth and security**: [Auth](docs/auth/index.md), [Security](docs/security/index.md)
- **Shared services**: [Events](docs/events/index.md), [Logging](docs/logging/index.md), [Mail](docs/mail/index.md), [Cache](docs/cache/index.md), [Queue](docs/queue/index.md)
- **Rendering and forms**: [View](docs/view/index.md), [Form](docs/form/index.md)
- **Tooling and tests**: [Console](docs/console/index.md), [Testing](docs/testing/index.md), [Utilities](docs/utilities/index.md)

## Repository development

Install the repository's dev dependencies and run the baseline checks:

```bash
composer validate --strict
composer install
composer audit --no-interaction
composer cs
composer phpstan
composer phpstan-tests
composer test:core
```

Additional local suites cover SQLite and external runtime integrations:

```bash
composer test:sqlite
composer test:external
```

The SQLite suite requires `ext-pdo` and `ext-pdo_sqlite`. The external suite requires `ext-curl` and a `google-chrome` executable for PDF rendering.

Service-backed suites are available for dependencies defined in `docker-compose.yml`:

```bash
docker compose up -d mysql
composer test:mysql
```

Available service-backed suites include `mariadb`, `mysql`, `postgres`, `redis`, `memcached`, and `smtp`.

## License

FyreFramework is released under the [MIT License](LICENSE).
