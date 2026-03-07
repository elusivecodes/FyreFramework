# Contextual attributes

Contextual attributes enable *contextual injection*: resolving a parameter value from runtime context instead of from an explicit argument or a container binding.

## Table of Contents

- [Purpose](#purpose)
- [When to use contextual attributes](#when-to-use-contextual-attributes)
- [How resolution works](#how-resolution-works)
- [Built-in attributes](#built-in-attributes)
  - [Cache](#cache)
  - [Config](#config)
  - [CurrentUser](#currentuser)
  - [DB](#db)
  - [Encryption](#encryption)
  - [Log](#log)
  - [Mail](#mail)
  - [ORM](#orm)
  - [RouteArgument](#routeargument)
- [Custom contextual attributes](#custom-contextual-attributes)
- [Attribute handler overrides](#attribute-handler-overrides)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Contextual attributes participate in the container’s dependency resolution when it resolves parameters for:

- `Container::build()` (constructor parameters)
- `Container::call()` (callable parameters)

They are useful when a value should come from “what’s happening right now” (the current request, current user, selected connection key, etc.) rather than being an always-the-same container-managed service.

## When to use contextual attributes

Use contextual attributes when the value depends on runtime context and shouldn’t be modeled as a stable container-managed service:

- current request data (route arguments, request attributes)
- current identity/user
- “selected by key” services (a specific connection, logger, cache, mailer, encrypter, etc.)

Prefer regular dependency injection (type-hints and bindings) when you want the same service instance every time without depending on request/job context.

## How resolution works

When resolving a parameter, the container checks for a parameter attribute that is an instance of `ContextualAttribute` (using `ReflectionAttribute::IS_INSTANCEOF`). If one is present, the container resolves the attribute *before* it attempts to resolve the parameter by type-hint.

In short, parameter resolution priority is:

**named argument** → **contextual attribute** → **class type-hint** → **default/null** → **exception**

Resolution happens in one of two ways:

1. **Handler override**: if the container has a handler for the attribute class (registered via `Container::bindAttribute()`), the handler is executed via `Container::call()` and receives the attribute instance in an argument named `attribute`.
2. Otherwise, the container instantiates the attribute and calls `resolve(Container $container): mixed`.

If multiple matching contextual attributes are present on a parameter, the first one is used.

If no contextual attribute is present, the container falls back to type-hints, defaults, and nullability (see [Container](container.md) for the full resolution order).

## Built-in attributes

Fyre provides a set of built-in contextual parameter attributes under `Fyre\Core\Attributes\*` that extend `ContextualAttribute`. The container can resolve them when building objects or calling callables.

The examples below show function or method signatures. These parameters are only resolved when invoked via `Container::call()` or when a class is instantiated via `Container::build()`; PHP itself does not resolve them automatically.

Each attribute below follows the same shape:

- **Use**: attach it to a parameter: `fn(#[AttributeName(...)] Type $param) => ...`
- **Resolution**: the container instantiates the attribute and calls `resolve($container)` (unless overridden via `bindAttribute()`)

Attributes are listed alphabetically for quick lookup.

### Cache

- **Use**: `#[Cache(string $key = CacheManager::DEFAULT)]`
- **Resolves**: `Cacher` via `CacheManager::use($key)`

```php
use Fyre\Cache\Cacher;
use Fyre\Core\Attributes\Cache;

function cacheExample(#[Cache] Cacher $cacher): Cacher
{
    return $cacher;
}
```

### Config

- **Use**: `#[Config(string $key)]`
- **Resolves**: config value via `Config::get($key)` (dot-notation)

```php
use Fyre\Core\Attributes\Config;

function configExample(#[Config('App.name')] string|null $name): string|null
{
    return $name;
}
```

### CurrentUser

- **Use**: `#[CurrentUser]`
- **Resolves**: `Entity|null` via `Auth::user()`
- **Notes**: depends on auth context already being available for the current request or runtime flow

```php
use Fyre\Core\Attributes\CurrentUser;
use Fyre\ORM\Entity;

function currentUserExample(#[CurrentUser] Entity|null $currentUser): Entity|null
{
    return $currentUser;
}
```

### DB

- **Use**: `#[DB(string $key = ConnectionManager::DEFAULT)]`
- **Resolves**: `Connection` via `ConnectionManager::use($key)`

```php
use Fyre\Core\Attributes\DB;
use Fyre\DB\Connection;

function dbExample(#[DB] Connection $db): Connection
{
    return $db;
}
```

### Encryption

- **Use**: `#[Encryption(string $key = EncryptionManager::DEFAULT)]`
- **Resolves**: `Encrypter` via `EncryptionManager::use($key)`

```php
use Fyre\Core\Attributes\Encryption;
use Fyre\Security\Encryption\Encrypter;

function encryptionExample(#[Encryption('openssl')] Encrypter $enc): Encrypter
{
    return $enc;
}
```

### Log

- **Use**: `#[Log(string $key = LogManager::DEFAULT)]`
- **Resolves**: `Psr\Log\LoggerInterface` via `LogManager::use($key)`

```php
use Fyre\Core\Attributes\Log;
use Psr\Log\LoggerInterface;

function logExample(#[Log('other')] LoggerInterface $log): LoggerInterface
{
    return $log;
}
```

### Mail

- **Use**: `#[Mail(string $key = MailManager::DEFAULT)]`
- **Resolves**: `Mailer` via `MailManager::use($key)`

```php
use Fyre\Core\Attributes\Mail;
use Fyre\Mail\Mailer;

function mailExample(#[Mail('other')] Mailer $mail): Mailer
{
    return $mail;
}
```

### ORM

- **Use**: `#[ORM(string $alias)]`
- **Resolves**: `Model` via `ModelRegistry::use($alias)`

```php
use Fyre\Core\Attributes\ORM;
use Fyre\ORM\Model;

function ormExample(#[ORM('Test')] Model $m): Model
{
    return $m;
}
```

### RouteArgument

- **Use**: `#[RouteArgument(string $name)]`
- **Resolves**: route argument value from the current request’s `routeArguments` attribute (or `null` if missing)
- **Notes**: resolves the raw route argument value, not a binding-resolved entity

```php
use Fyre\Core\Attributes\RouteArgument;

function routeArgumentExample(#[RouteArgument('id')] int|null $routeId): int|null
{
    return $routeId;
}
```

## Custom contextual attributes

To create your own contextual attribute, define a parameter attribute class that extends `ContextualAttribute` and implements `resolve(Container $container): mixed`.

```php
use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Utility\FileSystem\File;

#[Attribute(Attribute::TARGET_PARAMETER)]
class StorageFile extends ContextualAttribute
{
    public function __construct(
        protected string $path
    ) {}

    public function resolve(Container $container): File
    {
        return $container->build(File::class, [
            'path' => $this->path,
            'create' => false,
        ]);
    }
}
```

Use it anywhere the container resolves parameters:

```php
$container = new Container();

$file = $container->call(
    static fn(#[StorageFile('storage/example.txt')] File $f): File => $f
);
```

## Attribute handler overrides

`Container::bindAttribute()` lets you override how an attribute is resolved without changing the attribute class itself. The handler is executed via `Container::call()` and receives the attribute instance in an argument named `attribute`.

```php
use Fyre\Core\Attributes\Config as ConfigAttribute;
use Fyre\Core\Container;

$container = new Container();

$container->bindAttribute(ConfigAttribute::class, static function(ConfigAttribute $attribute, Container $container): mixed {
    return $attribute->resolve($container);
});
```

## Behavior notes

A few behaviors are worth keeping in mind:

- If you pass an argument with the same name as the parameter, the container uses that value and does not evaluate contextual attributes for that parameter.
- If a parameter has a contextual attribute, the container uses it rather than resolving the type-hint directly.
- If multiple matching contextual attributes are present, only the first one is used, so stacking them is ineffective and should be avoided.
- `bindAttribute()` handlers are called via `Container::call()` with an argument named `attribute`, so accept a parameter named `$attribute` to receive it.

## Related

- [Container](container.md)
- [Auth](../auth/index.md)
- [Cache](../cache/index.md)
- [Config](config.md)
- [Database connections](../database/connections.md)
- [Encryption](../security/encryption.md)
- [Logging](../logging/index.md)
- [Mail](../mail/index.md)
- [ORM](../orm/index.md)
- [Route Bindings](../routing/route-bindings.md)
