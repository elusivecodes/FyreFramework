# Getting Started

Use this guide to install FyreFramework, check its requirements, and bootstrap a minimal application.

FyreFramework is distributed as a package rather than an application skeleton. Your application repository defines its entry points, routes, configuration paths, and project layout around the framework.

## Table of Contents

- [Start here](#start-here)
- [Requirements](#requirements)
- [Installation](#installation)
- [Application bootstrap](#application-bootstrap)
- [Next steps](#next-steps)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual setup is:

1. Check that your PHP installation provides the extensions needed by the parts of the framework you plan to use.
2. Install `fyre/framework` with Composer.
3. Extend `Engine` when you want the framework's common application services available by default.
4. Add the entry points, routes, middleware, and configuration required by your application.

If you only need an individual subsystem, install the same package and resolve or construct that subsystem directly instead of building around `Engine`.

## Requirements

FyreFramework requires:

- PHP 8.5 or later
- `ext-intl`
- `ext-mbstring`

Database connections also require `ext-pdo` and the matching PDO driver, such as `ext-pdo_mysql`, `ext-pdo_pgsql`, or `ext-pdo_sqlite`.

Some features require additional extensions:

- `ext-curl` for HTTP client requests
- `ext-exif` for EXIF orientation detection during image manipulation
- `ext-gd` for image manipulation
- `ext-memcached` for the Memcached cache handler
- `ext-openssl` for the OpenSSL encryption handler
- `ext-pcntl` for queue workers and asynchronous promises
- `ext-posix` for asynchronous promises
- `ext-redis` for Redis cache and queue handlers
- `ext-sockets` for asynchronous promises

The framework has no third-party runtime dependencies beyond PSR interfaces (`psr/*`).

## Installation

Install the framework package with Composer:

```bash
composer require fyre/framework
```

Composer installs the framework and makes its classes available through the generated autoloader.

## Application bootstrap

A common application bootstrap extends `Fyre\Core\Engine`, customizes the middleware queue, and shares the application instance with the framework helpers:

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

Your HTTP or console entry point is responsible for loading this bootstrap and handing control to the application-specific request or command flow.

## Next steps

Continue with the area that matches what you are building:

- [Engine](core/engine.md) - configure the application services provided by the default engine
- [Config](core/config.md) - define and load application configuration
- [HTTP Middleware](http/middleware.md) - assemble the HTTP request pipeline
- [Routing](routing/index.md) - define routes and dispatch request handlers
- [Console](console/index.md) - build and run console commands
- [Database](database/index.md) - configure connections and build queries
- [ORM](orm/index.md) - work with models, entities, and relationships
- [Testing](testing/index.md) - test framework-powered application code

## Behavior notes

A few setup details are worth keeping in mind:

- The framework package does not prescribe an application directory layout or generate entry points.
- Only enable and configure the subsystems your application uses.
- Optional PHP extensions are required only when the corresponding feature is used.

## Related

- [Core](core/index.md)
- [HTTP](http/index.md)
- [Console](console/index.md)
- [Testing](testing/index.md)
