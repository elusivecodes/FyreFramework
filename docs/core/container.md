# Container

Use `Fyre\Core\Container` when you want explicit control over dependency injection, object creation, and service lifetimes.

Most applications work through [Engine](engine.md), but the underlying container is useful for tests, manual composition, and lower-level framework work.

## Table of Contents

- [Start here](#start-here)
- [Core workflows](#core-workflows)
  - [Resolve a service with `use()`](#resolve-a-service-with-use)
  - [Build a new object with `build()`](#build-a-new-object-with-build)
  - [Call code with `call()`](#call-code-with-call)
- [Bindings and lifetimes](#bindings-and-lifetimes)
- [Dependency resolution](#dependency-resolution)
- [Contextual attributes](#contextual-attributes)
- [Method guide](#method-guide)
  - [Resolving and invoking](#resolving-and-invoking)
  - [Global instance](#global-instance)
  - [Binding services](#binding-services)
  - [Scoping and cleanup](#scoping-and-cleanup)
  - [Contextual attribute handlers](#contextual-attribute-handlers)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The container gives you three core capabilities:

- resolve a service with `use()`
- build a fresh object with `build()`
- call a function or method with injected dependencies via `call()`

`Container` also implements PSR-11:

- `get()` delegates to `use()`
- `has()` checks whether an alias maps to a factory or instantiable class

## Core workflows

### Resolve a service with `use()`

`use($alias)` resolves a class name or custom alias and returns the matching service or value.

```php
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Lang;

$container = new Container();

$container->singleton(Config::class);
$container->singleton(Lang::class);

$config = $container->use(Config::class)
    ->addPath('/path/to/config')
    ->load('app');

$lang = $container->use(Lang::class);
```

When a shared service has already been resolved, `use()` returns that same instance unless you pass manual arguments.

### Build a new object with `build()`

`build($className)` always creates a new instance of the class you ask for, while still resolving its dependencies through the container.

```php
$container = new Container();
$container->singleton(Config::class);

$langA = $container->build(Lang::class);
$langB = $container->build(Lang::class);
```

### Call code with `call()`

`call($callable)` runs a closure, method, or invokable object and resolves its parameters through the same rules used by `build()`.

```php
use Fyre\Core\Attributes\Config as ConfigAttribute;
use Fyre\Core\Config;

$container = new Container();
$container->singleton(Config::class);
$container->use(Config::class)->set('App.name', 'MyApp');

$name = $container->call(
    static fn(#[ConfigAttribute('App.name')] string $appName): string => $appName
);
```

## Bindings and lifetimes

Bindings tell the container how to resolve an alias. Lifetimes tell it whether the result should be reused.

- **Unbound class name**: if the alias is a concrete instantiable class, the container can build it on demand.
- `bind()`: bind an alias to a class name or factory closure.
- `singleton()`: bind a shared service that stays available for the life of the container.
- `scoped()`: bind a shared service that can be cleared later with `clearScoped()`.
- `instance()`: bind an alias directly to a specific value or object.
- `replaceInstance()`: replace the current cached instance without removing its existing binding or scoped lifetime.

String-to-string bindings are common for interfaces:

```php
use Fyre\Log\Handlers\ArrayLogger;
use Psr\Log\LoggerInterface;

$container->singleton(LoggerInterface::class, ArrayLogger::class);

$logger = $container->use(LoggerInterface::class);
```

If you pass manual arguments while resolving a shared binding, the returned instance is not stored as the shared instance.

## Dependency resolution

For both `build()` and `call()`, parameters are resolved in this order:

1. a matching named argument you pass explicitly
2. the next positional argument you pass explicitly
3. a contextual attribute on the parameter
4. a class or interface type-hint
5. the parameter default value or `null` when allowed
6. otherwise, a `ContainerException`

That keeps constructor and method signatures predictable while still allowing targeted overrides.

## Contextual attributes

Contextual attributes let a parameter come from runtime context instead of a normal binding. Common examples include:

- a config value with `#[Config('App.name')]`
- the current user with `#[CurrentUser]`
- a route argument with `#[RouteArgument('id')]`
- a keyed service such as `#[DB('readonly')]` or `#[Cache('redis')]`

For the full list and custom attribute examples, see [Contextual attributes](contextual-attributes.md).

## Method guide

This section focuses on the methods you are most likely to use when wiring services and resolving dependencies.

Unless noted otherwise, examples below assume:

```php
use Fyre\Core\Container;

$container = new Container();
```

### Resolving and invoking

#### **Resolve an alias** (`use()`)

Resolves an alias into a service or value.

Arguments:
- `$alias` (`string`): a class name or custom alias.
- `$arguments` (`array`): optional named or positional arguments.

```php
use Fyre\Core\Config;
use Fyre\Core\Lang;

$container->singleton(Config::class);

$container->use(Config::class)->set('App.defaultLocale', 'en_US');
$lang = $container->use(Lang::class);
```

#### **PSR-11 lookup** (`get()`)

PSR-11 lookup method. In this container it behaves the same as `use()`.

Arguments:
- `$alias` (`string`): a class name or custom alias.

```php
$cache = $container->get('cache');
```

#### **PSR-11 presence check** (`has()`)

Checks whether an alias maps to a factory or instantiable class without resolving it.

Arguments:
- `$alias` (`string`): a class name or custom alias.

```php
if ($container->has('cache')) {
    $cache = $container->get('cache');
}
```

#### **Build a new object** (`build()`)

Builds a fresh instance of the exact class you pass in.

Arguments:
- `$className` (`string`): the class to instantiate.
- `$arguments` (`array`): optional named or positional constructor arguments.

```php
$lang = $container->build(Lang::class);
```

#### **Call a callable with injection** (`call()`)

Invokes a callable and resolves its parameters through the container.

Arguments:
- `$callable` (`array|object|string`): a closure, invokable object, object method, class method, or `'ClassName::method'`.
- `$arguments` (`array`): optional named or positional arguments.

```php
use Fyre\Core\Attributes\Config as ConfigAttribute;
use Fyre\Core\Config;

$container->singleton(Config::class);
$container->use(Config::class)->set('App.name', 'MyApp');

$result = $container->call(
    static fn(#[ConfigAttribute('App.name')] string $appName): string => $appName
);
```

### Global instance

#### **Get the global container instance** (`getInstance()`)

Returns the current global container instance.

```php
$container = Container::getInstance();
```

#### **Set the global container instance** (`setInstance()`)

Replaces the global container instance.

Arguments:
- `$instance` (`Container`): the container instance.

```php
Container::setInstance($container);
```

### Binding services

#### **Bind an alias to a class or factory** (`bind()`)

Defines how an alias should be resolved.

Arguments:
- `$alias` (`string`): the alias you will resolve.
- `$factory` (`Closure|string|null`): a factory closure or class name.
- `$shared` (`bool`): whether the result should be shared.
- `$scoped` (`bool`): whether the shared result should be cleared by `clearScoped()`.

```php
use Fyre\Cache\Handlers\Array\ArrayCacher;

$container->bind('cache', ArrayCacher::class);
$cache = $container->use('cache');
```

#### **Bind a singleton** (`singleton()`)

Shorthand for a shared binding.

Arguments:
- `$alias` (`string`): the alias to bind.
- `$factory` (`Closure|string|null`): a factory closure or class name.

```php
$container->singleton(Config::class);
```

#### **Bind a scoped service** (`scoped()`)

Shorthand for a shared binding that can later be cleared with `clearScoped()`.

Arguments:
- `$alias` (`string`): the alias to bind.
- `$factory` (`Closure|string|null`): a factory closure or class name.

```php
use Fyre\TestSuite\Timer;

$container->scoped(Timer::class);
```

#### **Bind a concrete instance** (`instance()`)

Pins an alias to a specific value or object.

Arguments:
- `$alias` (`string`): the alias to bind.
- `$instance` (`mixed`): the value to return.

```php
$config = new Config();
$container->instance(Config::class, $config);
```

#### **Replace a cached instance** (`replaceInstance()`)

Replaces the current cached instance while preserving the alias binding. Dependents of the previous instance are also cleared.

Arguments:
- `$alias` (`string`): the alias whose cached instance should be replaced.
- `$instance` (`mixed`): the replacement value.

```php
use Fyre\Http\ServerRequest;

$request = $container->use(ServerRequest::class)
    ->withAttribute('user', $user);

$container->replaceInstance(ServerRequest::class, $request);
```

Unlike `instance()`, this does not remove the existing binding. If the alias is scoped, `clearScoped()` removes the replacement and the next resolution uses the preserved binding again.

### Scoping and cleanup

#### **Clear scoped services** (`clearScoped()`)

Clears all scoped shared services while keeping their bindings.

```php
$container->clearScoped();
```

#### **Unset a cached instance** (`unset()`)

Removes the cached instance for an alias.

Arguments:
- `$alias` (`string`): the alias to unset.
- `$unsetDependents` (`bool`): whether to also unset dependent shared services.

```php
$container->unset(Config::class);
```

#### **Remove an alias from scoping** (`unscoped()`)

Keeps the binding but stops `clearScoped()` from clearing it.

Arguments:
- `$alias` (`string`): the alias to un-scope.

```php
$container->unscoped(Timer::class);
```

### Contextual attribute handlers

#### **Override contextual attribute resolution** (`bindAttribute()`)

Registers a handler for a contextual attribute. This is most useful in tests or when you want custom resolution behavior.

Arguments:
- `$attribute` (`string`): the attribute class name.
- `$handler` (`Closure`): the resolver callback.

```php
use Fyre\Core\Attributes\CurrentUser;

$container->bindAttribute(CurrentUser::class, static fn(CurrentUser $attribute) => null);
```

## Behavior notes

A few practical details are worth keeping in mind:

- Shared services are cached only when you resolve them without manual arguments.
- `call([ClassName::class, 'method'])` will instantiate the class first when the method is not static.
- Rebinding a service clears its cached shared instance.
- `instance()` replaces the binding itself, while `replaceInstance()` preserves the binding and replaces only its current cached value.
- `clearScoped()` is the main tool for dropping request-specific or job-specific state in long-running processes.

## Related

- [Engine](engine.md)
- [Contextual attributes](contextual-attributes.md)
- [Route Bindings](../routing/route-bindings.md)
- [Worker](../queue/worker.md)
