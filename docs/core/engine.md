# Engine

`Fyre\Core\Engine` is the framework’s application container: it extends `Fyre\Core\Container` and pre-registers the core services your code can resolve via dependency injection.
Because it is a container, `Engine` inherits the same binding and resolution APIs as `Container` (see [Container](container.md)).

## Table of Contents

- [Purpose](#purpose)
- [How it works](#how-it-works)
- [Creating and sharing the application instance](#creating-and-sharing-the-application-instance)
- [Using the `app()` helper](#using-the-app-helper)
- [Service lifetimes (singleton vs scoped)](#service-lifetimes-singleton-vs-scoped)
- [Default bindings and services](#default-bindings-and-services)
  - [Existing instance](#existing-instance)
  - [Binding](#binding)
  - [Scoped services](#scoped-services)
  - [Singleton services](#singleton-services)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `Engine` as the base application container you extend and configure (for example, `Application extends Engine`).

In your subclass, you typically:

- register application-specific bindings
- perform runtime initialization in your own application method such as `boot()` (invoked via `$app->call()` so dependencies can be injected)
- customize the middleware queue by overriding `middleware()`

`Engine` itself does not define a `boot()` hook; that is an application convention.

```php
use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Http\MiddlewareQueue;
use Fyre\Log\Handlers\FileLogger;
use Psr\Log\LoggerInterface;

class Application extends Engine
{
    public function boot(Config $config): void
    {
        // Register application-specific bindings.
        $this->singleton(LoggerInterface::class, FileLogger::class);

        $config
            ->load('bootstrap');
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

## How it works

`Engine` runs the container constructor logic plus its own wiring: it registers default aliases and services up front so application code can type-hint and resolve common framework services without binding everything manually.

On top of `Container`, it also:

- registers default middleware aliases and builds the application middleware queue
- provides namespace and path defaults for common registries and locators
- loads `CONFIG/routes.php` when constructing the default `Router`

You can add your own bindings in an `Engine` subclass and then keep the rest of your application code focused on behavior (services, controllers, jobs), relying on `build()`, `use()`, and `call()` for consistent dependency injection.

## Creating and sharing the application instance

To access a single application container globally, construct your application with a configured loader, store it as the shared instance, and then run any application bootstrap method you define:

```php
use Fyre\Core\Loader;

$loader = (new Loader())
    ->loadComposer('vendor/autoload.php')
    ->register();

$app = new Application($loader);
Application::setInstance($app);

// Run application initialization (with dependency injection).
$app->call([$app, 'boot']);
```

## Using the `app()` helper

The `app()` helper provides a shorthand for accessing the shared `Engine` instance or resolving a service from it.

Under the hood, `app()` calls `Engine::getInstance()` and then either returns that instance (when called with no arguments) or resolves an alias via `$app->use($alias, $arguments)`.

```php
$app = app();
$config = app(Config::class);
```

For more helper examples, see [Helpers](helpers.md).

## Service lifetimes (singleton vs scoped)

Both lifetimes are shared (cached) when resolved without manual constructor arguments, but they are intended for different runtime boundaries:

- **Singleton**: a long-lived shared service instance.
- **Scoped**: a shared service instance intended to be cleared at a boundary you control (while keeping the binding), so the next resolution gets a fresh instance.

```php
$configA = $app->use(Config::class);
$configB = $app->use(Config::class, ['paths' => ['config']]);
```

To clear all scoped instances (including dependents) while keeping bindings:

```php
$app->clearScoped();
```

## Default bindings and services

`Engine` registers default bindings and services in its constructor.
For the authoritative list, see `src/Core/Engine.php`.

### Existing instance

- `Fyre\Core\Loader` (via `instance()`): the loader instance the engine is constructed with.

### Binding

- `Psr\Http\Message\ServerRequestInterface` → `Fyre\Http\ServerRequest` (via `bind()`): resolves the framework request implementation when a PSR request is required.

### Scoped services

- `Fyre\Auth\Auth`
- `Fyre\Http\MiddlewareQueue` (built, then passed through `Engine::middleware()` for customization)
- `Fyre\Http\MiddlewareRegistry` (pre-mapped with default middleware aliases)
- `Fyre\Http\ServerRequest`
- `Fyre\Security\CsrfProtection`
- `Fyre\TestSuite\Benchmark`
- `Fyre\TestSuite\Timer`

### Singleton services

- `Fyre\Auth\Identifier`
- `Fyre\Auth\PolicyRegistry` (adds the `App\Policies` namespace)
- `Fyre\Cache\CacheManager`
- `Fyre\Console\CommandRunner` (adds the `App\Commands` and `Fyre\Commands` namespaces)
- `Fyre\Console\Console`
- `Fyre\Core\Config` (adds `CONFIG` plus the framework’s built-in `config/` directory to its search paths)
- `Fyre\Core\ErrorHandler`
- `Fyre\Core\Lang` (adds `LANG` plus the framework’s built-in `lang/` directory to its search paths)
- `Fyre\Core\Make`
- `Fyre\DB\ConnectionManager`
- `Fyre\DB\Forge\ForgeRegistry`
- `Fyre\DB\Migration\MigrationRunner` (adds the `App\Migrations` namespace)
- `Fyre\DB\Schema\SchemaRegistry`
- `Fyre\DB\TypeParser`
- `Fyre\Event\EventManager` (constructed with `parentEventManager` set to `null`)
- `Fyre\Http\Session\Session`
- `Fyre\Log\LogManager`
- `Fyre\Mail\MailManager`
- `Fyre\ORM\EntityLocator` (adds the `App\Entities` namespace)
- `Fyre\ORM\ModelRegistry` (adds the `App\Models` namespace)
- `Fyre\Queue\QueueManager`
- `Fyre\Router\RouteLocator`
- `Fyre\Router\Router` (loads `CONFIG/routes.php` if it exists)
- `Fyre\Security\ContentSecurityPolicy`
- `Fyre\Security\Encryption\EncryptionManager`
- `Fyre\TestSuite\Fixture\FixtureRegistry` (adds the `Tests\Fixtures` namespace)
- `Fyre\Utility\FormBuilder`
- `Fyre\Utility\Formatter`
- `Fyre\Utility\HtmlHelper`
- `Fyre\Utility\Inflector`
- `Fyre\View\CellRegistry` (adds the `App\Cells` namespace)
- `Fyre\View\HelperRegistry` (adds the `App\Helpers` namespace)
- `Fyre\View\TemplateLocator` (adds the `TEMPLATES` path)

## Behavior notes

A few behaviors are worth keeping in mind:

- `Engine::getInstance()` (and `Application::getInstance()`) lazily creates a new instance using `new Loader()` if no instance has been set via `setInstance()`. Create and share your application instance early if you rely on loader-driven discovery features.
- Shared lifetimes (`singleton()` / `scoped()`) are cached only when resolved without manual arguments. If you pass constructor arguments to `use()`, the resulting instance is not cached as the shared instance.
- When `MiddlewareQueue` is built, `Engine` passes it through `middleware()` and then dispatches the `Engine.buildMiddleware` event before returning it.

## Related

- [Container](container.md)
- [Loader](loader.md)
- [Helpers](helpers.md)
- [HTTP Middleware](../http/middleware.md)
- [Event Manager](../events/event-manager.md)
