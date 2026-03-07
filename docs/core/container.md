# Container

`Fyre\Core\Container` is Fyre’s dependency injection container: it builds objects, resolves dependencies, and controls whether instances are reused or created fresh.

## Table of Contents

- [Purpose](#purpose)
- [Core workflows](#core-workflows)
  - [Resolve an instance with `use()`](#resolve-an-instance-with-use)
  - [Build a new object with `build()`](#build-a-new-object-with-build)
  - [Invoke callables with `call()`](#invoke-callables-with-call)
- [Bindings and lifetimes](#bindings-and-lifetimes)
- [Dependency resolution rules](#dependency-resolution-rules)
- [Contextual attributes](#contextual-attributes)
- [Method guide](#method-guide)
  - [Resolving and invoking](#resolving-and-invoking)
  - [Global instance](#global-instance)
  - [Binding services](#binding-services)
  - [Scoping and cleanup](#scoping-and-cleanup)
  - [Contextual attribute handlers](#contextual-attribute-handlers)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

The container exists to centralize object creation and dependency wiring so application code can stay focused on behavior: constructors stay explicit, and the container supplies dependencies when building objects or calling callables.

In a typical application, you work through `Fyre\Core\Engine`, which extends `Container` and registers the framework's default bindings. This page focuses on the underlying `Container` API, which is also useful for manual composition and tests.

The container implements `Psr\Container\ContainerInterface`:

- `get()` delegates to `use()`
- `has()` checks whether an alias can be resolved (including following string-to-string bindings)

By default, the container also binds itself (`Fyre\Core\Container`) as a concrete instance, so you can type-hint `Container` and have it injected.

## Core workflows

### Resolve an instance with `use()`

`use($alias)` resolves an alias to an object/value by:

- returning a cached shared instance when available (and no manual arguments are provided)
- otherwise resolving the alias to either:
  - a class name (built via `build()`), or
  - a factory closure (invoked via `call()`), or
  - another alias (followed recursively)

```php
use Fyre\Core\Container;
use Fyre\Core\Config;
use Fyre\Core\Lang;

// Low-level/manual setup example.
$container = new Container();

// Bind shared services you plan to reuse across the application lifetime.
$container->singleton(Config::class);
$container->singleton(Lang::class);

$config = $container->use(Config::class)
    ->addPath('config')
    ->load('app');

// Lang depends on Config; the container wires it automatically.
$lang = $container->use(Lang::class);
```

### Build a new object with `build()`

`build($className)` constructs a class directly and injects constructor dependencies as needed. It does not consult bindings for the class name; it always attempts to instantiate the class you pass in. Constructor dependencies are still resolved through the container.

```php
// Low-level/manual setup example.
$container = new Container();
$container->singleton(Config::class);

// Always returns a new instance (no caching)…
$langA = $container->build(Lang::class);
$langB = $container->build(Lang::class);

// …but constructor dependencies still come from the container.
// (Here, Lang receives the shared Config instance.)
```

### Invoke callables with `call()`

`call($callable)` invokes a callable and resolves its parameters the same way `build()` resolves constructor parameters. Supported callable forms include:

- closures / invokable objects
- `[$object, 'method']`
- `[ClassName::class, 'method']` (the container instantiates the target for non-static methods)
- `'ClassName::method'`

```php
use Fyre\Core\Attributes\Config as ConfigAttribute;

// Low-level/manual setup example.
$container = new Container();

// Bind the shared config service and set a value.
$container->singleton(Config::class);
$container->use(Config::class)->set('App.name', 'MyApp');

$name = $container->call(
    static fn(#[ConfigAttribute('App.name')] string $appName): string => $appName
);
```

## Bindings and lifetimes

Bindings map an alias to a class name or factory closure. Lifetimes control whether the resolved value is cached.

- **Unbound alias**: if the alias is a concrete, instantiable class name, `use()` builds it on demand.
- `bind()`: bind an alias to a class name or factory closure (not shared by default).
- `singleton()`: shared binding; the resolved instance is cached.
- `scoped()`: shared binding intended to be cleared at runtime boundaries via `clearScoped()`.
- `instance()`: bind an alias directly to a specific instance/value.
- Rebinding an alias clears any cached instance for that alias and removes its scoped status before applying the new binding.

String-to-string bindings are commonly used to bind interfaces to concrete implementations:

```php
use Fyre\Log\Handlers\ArrayLogger;
use Psr\Log\LoggerInterface;

$container->singleton(LoggerInterface::class, ArrayLogger::class);
$logger = $container->use(LoggerInterface::class);
```

Shared bindings are cached only when resolved with **no manual arguments**. If manual arguments are provided, the instance is returned but not stored as the shared instance.

```php
use Fyre\Utility\DateTime\DateTime;

$container->singleton(DateTime::class);

$shared = $container->use(DateTime::class);
$notShared = $container->use(DateTime::class, ['time' => '2020-01-01 00:00:00']);
```

## Dependency resolution rules

Parameter resolution follows a consistent order for both `build()` and `call()`:

1. **Named arguments**: if the arguments array contains a key matching the parameter name, that value is used first.
2. **Contextual attribute**: if the parameter has an attribute that is an instance of `Fyre\Core\ContextualAttribute`, it is resolved (see [Contextual attributes](#contextual-attributes)).
3. **Class type-hint**: if the parameter type is a non-built-in class/interface name, the container resolves it via `use($typeName)`.
   - `self` and `parent` are supported for class-typed parameters and resolve relative to the declaring class.
4. **Fallbacks**:
   - default value, when available
   - `null`, when allowed
   - otherwise, a `Fyre\Core\Exceptions\ContainerException` is thrown (unless the parameter is variadic)

Any arguments left over after named arguments are applied are appended and passed positionally.

Caching note: if you resolve a shared binding via `use()` with manual arguments, the returned instance is not cached as the shared instance.

Cycle detection note: `use()` detects alias recursion, and `build()` / `call()` detect recursive class dependency construction and throw `Fyre\Core\Exceptions\ContainerException` instead of recursing indefinitely.

## Contextual attributes

Contextual attributes allow parameter injection from runtime context rather than from a container binding. Parameters annotated with an attribute that extends `Fyre\Core\ContextualAttribute` are resolved before type-hints.

Two resolution paths exist:

- If a handler is registered via `bindAttribute($attributeClass, $handler)`, the handler is executed via `call()` and receives the attribute instance as an argument named `attribute`.
- Otherwise, the container instantiates the attribute and calls `$attribute->resolve($container)`.

If multiple matching contextual attributes are present on a parameter, the first one is used.

The built-in attributes live under `Fyre\Core\Attributes\*` (for example `Fyre\Core\Attributes\Config` and `Fyre\Core\Attributes\RouteArgument`).

For the broader contextual injection model, see [Contextual attributes](contextual-attributes.md). For route argument injection, see [Route Bindings](../routing/route-bindings.md).

## Method guide

This section focuses on the methods you’ll use most when wiring services and resolving dependencies.

Unless noted otherwise, examples below assume you already have a `$container` instance:

```php
$container = new Container();
```

### Resolving and invoking

#### **Resolve an alias (service lookup)** (`use()`)

Resolves an alias into an instance/value.

Arguments:
- `$alias` (`string`): a class name or a custom alias.
- `$arguments` (`array`): optional manual arguments for `build()`/`call()` (named arguments match parameter names).

```php
$container->singleton(Config::class);

$container->use(Config::class)->set('App.defaultLocale', 'en_US');

// Lang is auto-wired because it type-hints Config in its constructor.
$lang = $container->use(Lang::class);
```

#### **PSR-11 service lookup** (`get()`)

PSR-11 `ContainerInterface::get()`. In this container, `get()` delegates to `use()`.

Arguments:
- `$alias` (`string`): a class name or a custom alias.

```php
use Fyre\Cache\Handlers\ArrayCacher;

$container->bind('cache', ArrayCacher::class);

$cache = $container->get('cache');
```

#### **PSR-11 service presence check** (`has()`)

PSR-11 `ContainerInterface::has()`. `has()` follows string-to-string bindings and returns whether the alias can be resolved.

Arguments:
- `$alias` (`string`): a class name or a custom alias.

```php
$container->bind('cache', ArrayCacher::class);

if ($container->has('cache')) {
    $cache = $container->get('cache');
}
```

#### **Build a new object (no binding lookup)** (`build()`)

Instantiates the exact class you provide and injects constructor dependencies. Does not consult bindings and does not cache.

Arguments:
- `$className` (`string`): the concrete class name to instantiate.
- `$arguments` (`array`): optional constructor arguments (named arguments match parameter names).

```php
$container->singleton(Config::class);

$lang = $container->build(Lang::class);
```

#### **Call a callable with dependency injection** (`call()`)

Invokes a callable and resolves its parameters using the same rules as `build()`.

Arguments:
- `$callable` (`array|object|string`): a closure, invokable object, `[$object, 'method']`, `[ClassName::class, 'method']`, or `'ClassName::method'`.
- `$arguments` (`array`): optional manual arguments (named arguments match parameter names).

```php
$container->singleton(Config::class);
$container->use(Config::class)->set('App.name', 'MyApp');

$result = $container->call(
    static fn(#[ConfigAttribute('App.name')] string $appName): string => $appName
);
```

### Global instance

#### **Get the global container instance** (`getInstance()`)

Returns the current global `Container` instance, creating it on first use.

```php
$container = Container::getInstance();
$container->singleton(Config::class);
```

#### **Set the global container instance** (`setInstance()`)

Replaces the global container instance returned by `getInstance()`.

Arguments:
- `$instance` (`Container`): the Container instance.

```php
$container = new Container();

Container::setInstance($container);
```

### Binding services

#### **Bind an alias to a factory or class** (`bind()`)

Defines how an alias is resolved. Not shared unless `$shared` is `true`.

Arguments:
- `$alias` (`string`): the name you will resolve with `use()`.
- `$factory` (`Closure|string|null`): a factory closure or class name (or `null` to use the alias as the class name).
- `$shared` (`bool`): cache the resolved value when called with no manual arguments.
- `$scoped` (`bool`): mark the shared value as scoped (cleared by `clearScoped()`).

```php
$container->bind('cache', ArrayCacher::class);
$cache = $container->use('cache');
```

#### **Bind a shared singleton service** (`singleton()`)

Shorthand for `bind(..., shared: true)`. Cached on first resolve (when called with no manual arguments).

Arguments:
- `$alias` (`string`): the alias (often a class name).
- `$factory` (`Closure|string|null`): a factory closure or class name (or `null` to use the alias as the class name).

```php
$container->singleton(Config::class);

$configA = $container->use(Config::class);
$configB = $container->use(Config::class);
```

#### **Bind a shared scoped service** (`scoped()`)

Shorthand for `bind(..., shared: true, scoped: true)`. Cached, but intended to be cleared per request/job via `clearScoped()`.

Arguments:
- `$alias` (`string`): the alias (often a class name).
- `$factory` (`Closure|string|null`): a factory closure or class name (or `null` to use the alias as the class name).

```php
use Fyre\TestSuite\Timer;

$container->scoped(Timer::class);

$timer = $container->use(Timer::class);
$container->clearScoped(); // next resolve gets a fresh Timer
```

#### **Bind a concrete instance/value** (`instance()`)

Pins an alias to a specific instance/value.

Arguments:
- `$alias` (`string`): the alias to bind.
- `$instance` (`mixed`): the instance/value to return from `use($alias)`.

```php
$config = new Config();
$config->set('App.name', 'MyApp');

$container->instance(Config::class, $config);
```

### Scoping and cleanup

#### **Clear scoped instances** (`clearScoped()`)

Unsets all *scoped* cached instances, but keeps the bindings. Any tracked dependent shared instances are also unset.

```php
$container->clearScoped();
```

#### **Unset a cached instance** (`unset()`)

Removes the cached instance for an alias. If `$unsetDependents` is `true`, it also unsets any dependents that were tracked when the instance was first cached.

Arguments:
- `$alias` (`string`): the alias to unset.
- `$unsetDependents` (`bool`): whether to unset tracked dependents.

```php
$container->singleton(Config::class);

$configA = $container->use(Config::class);
$container->unset(Config::class);

$configB = $container->use(Config::class);
```

#### **Remove an alias from scoping** (`unscoped()`)

Removes an alias from the scoped set. The binding and any cached instance remain; the alias simply won’t be cleared by `clearScoped()`.

Arguments:
- `$alias` (`string`): the alias to un-scope.

```php
$container->scoped(Timer::class);

$timerA = $container->use(Timer::class);
// Keep the instance, but prevent clearScoped() from clearing it.
$container->unscoped(Timer::class);

$container->clearScoped();
$timerB = $container->use(Timer::class);
```

### Contextual attribute handlers

#### **Override contextual attribute resolution** (`bindAttribute()`)

Registers a handler that resolves a contextual attribute value. The handler is executed via `call()` and receives an `$attribute` argument.

Arguments:
- `$attribute` (`string`): the attribute class name (must extend `Fyre\Core\ContextualAttribute`).
- `$handler` (`Closure`): the resolver; receives an `$attribute` parameter.

```php
use Fyre\Core\Attributes\CurrentUser;

// In a test, force #[CurrentUser] to always resolve to null.
$container->bindAttribute(CurrentUser::class, static fn(CurrentUser $attribute) => null);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- If an alias resolves (directly or indirectly) back to itself, `use()` throws a `ContainerException`.
- If class construction would recurse back into a class already in the build stack, resolution fails with a `ContainerException` rather than infinite recursion.
- When you call `[ClassName::class, 'method']` and the method is not static, `call()` instantiates `ClassName` before invoking the method.
- Calling `bind()`, `singleton()`, `scoped()`, or `instance()` first removes any cached instance for the alias and removes its scoped status (and `scoped()` then re-marks it as scoped).
- `clearScoped()` unsets all scoped instances and also unsets tracked dependents.
- `unset($alias, true)` also unsets tracked dependents for that alias.
- Dependents are tracked only for dependencies that are already container-managed shared instances at resolution time, and tracking is identity-based.

When running long-lived processes (for example a queue worker), `clearScoped()` is the primary tool for dropping per-job/per-request state while keeping bindings intact; see [Worker](../queue/worker.md).

## Related

- [Contextual attributes](contextual-attributes.md)
- [Route Bindings](../routing/route-bindings.md)
- [Worker](../queue/worker.md)
