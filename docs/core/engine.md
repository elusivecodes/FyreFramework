# Engine

Use `Fyre\Core\Engine` as the base application class when you want Fyre's common services, middleware defaults, and discovery hooks already wired in.

Because `Engine` extends [Container](container.md), you can register bindings, resolve services, and call bootstrapped code from the same application object.

## Table of Contents

- [Start here](#start-here)
- [What Engine gives you](#what-engine-gives-you)
- [Creating and sharing the application instance](#creating-and-sharing-the-application-instance)
- [Using the `app()` helper](#using-the-app-helper)
- [Shared services](#shared-services)
- [Common services available by default](#common-services-available-by-default)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Most applications use `Engine` like this:

1. Extend `Engine`.
2. Add any application-specific bindings.
3. Customize the middleware queue by overriding `middleware()`.
4. Create and share one application instance early in bootstrap.

```php
use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Http\MiddlewareQueue;
use Fyre\Log\LogManager;
use Psr\Log\LoggerInterface;

class Application extends Engine
{
    public function boot(Config $config): void
    {
        $this->singleton(
            LoggerInterface::class,
            static fn(LogManager $logs): LoggerInterface => $logs->use()
        );

        $config->load('bootstrap');
    }

    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue
            ->add('error')
            ->add('router')
            ->add('bindings');
    }
}
```

`Engine` does not define a built-in `boot()` hook. Calling `boot()` is an application convention.

## What Engine gives you

On top of the normal container features, `Engine` gives you a ready-to-use application baseline:

- default middleware aliases and a middleware queue you can customize through `middleware()`
- common managers, registries, and framework services already registered in the container
- namespace and path defaults for discovery features such as commands, models, policies, templates, and migrations
- automatic route loading from `CONFIG/routes.php` when `CONFIG` is defined and the file exists

That means most application code can focus on behavior instead of container setup.

## Creating and sharing the application instance

Create the loader, pass it into your application, share the instance, and then run any application bootstrap method you define:

```php
use Fyre\Core\Loader;

$composer = require 'vendor/autoload.php';

$loader = new Loader()
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->register();

$app = new Application($loader);
Application::setInstance($app);

$app->call([$app, 'boot']);
```

Sharing the instance early matters because helpers such as `app()`, `config()`, and `view()` resolve services from that shared application object.

## Using the `app()` helper

`app()` is the shortcut for working with the shared application instance.

- `app()` returns the shared engine instance
- `app(SomeClass::class)` resolves a service from the engine

```php
use Fyre\Core\Config;

$app = app();
$config = app(Config::class);
```

For more helper examples, see [Helpers](helpers.md).

## Shared services

`Engine` uses the container's shared-service lifetimes in two practical ways:

- **Singleton** services are shared for the life of the application instance.
- **Scoped** services are shared until you call `clearScoped()`, which is useful in long-running processes such as workers or some tests.

Most application code does not need to think about this often. The main time it matters is when you want to drop request-specific or job-specific state without rebuilding the whole application.

```php
$app->clearScoped();
```

For the lower-level lifetime rules, see [Container](container.md).

## Common services available by default

`Engine` pre-registers the services most applications reach for directly. Common examples include:

- **Core services**: `Config`, `Lang`, `ErrorHandler`, `Loader`, `Make`
- **HTTP runtime**: `ServerRequest`, `MiddlewareQueue`, `MiddlewareRegistry`, `Session`
- **Auth and security**: `Auth`, `Identifier`, `PolicyRegistry`, `CsrfProtection`, `ContentSecurityPolicy`, `EncryptionManager`
- **Data access**: `ConnectionManager`, `MigrationRunner`, `SchemaRegistry`, `TypeParser`, `ModelRegistry`, `EntityLocator`
- **Application services**: `CacheManager`, `LogManager`, `MailManager`, `QueueManager`
- **Console and discovery**: `CommandRunner`, `Console`, `RouteLocator`
- **View and utility services**: `Router`, `TemplateLocator`, `CellRegistry`, `HelperRegistry`, `FormBuilder`, `Formatter`, `HtmlHelper`, `Inflector`

Namespace and path defaults are also added for the usual application locations, including `App\Commands`, `App\Models`, `App\Policies`, `App\Entities`, `App\Cells`, `App\Helpers`, and the `TEMPLATES` path when that constant is defined.

## Behavior notes

A few practical details are worth keeping in mind:

- If you rely on loader-driven discovery features, set the shared application instance yourself instead of relying on lazy `getInstance()` creation.
- Shared services are cached only when you resolve them without manual constructor arguments.
- Override `middleware()` when you want to change the default application middleware queue.

## Related

- [Container](container.md)
- [Loader](loader.md)
- [Helpers](helpers.md)
- [HTTP Middleware](../http/middleware.md)
