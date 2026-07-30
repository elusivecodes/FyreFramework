# Contextual attributes

Contextual attributes let you inject a value from the current runtime context instead of from a normal container binding.

They are useful when a parameter depends on "what is happening right now", such as the current user, a route argument, or a specific keyed service.

## Table of Contents

- [Start here](#start-here)
- [When to use contextual attributes](#when-to-use-contextual-attributes)
- [How they are applied](#how-they-are-applied)
- [Built-in attributes](#built-in-attributes)
  - [`Cache`](#cache)
  - [`Config`](#config)
  - [`CurrentUser`](#currentuser)
  - [`DB`](#db)
  - [`Encryption`](#encryption)
  - [`Log`](#log)
  - [`Mail`](#mail)
  - [`ORM`](#orm)
  - [`RouteArgument`](#routeargument)
- [Custom contextual attributes](#custom-contextual-attributes)
- [Attribute handler overrides](#attribute-handler-overrides)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Contextual attributes are only used when the container is resolving parameters through:

- `Container::build()`
- `Container::call()`

They do not do anything when PHP calls the function or constructor directly.

## When to use contextual attributes

Use them when the value should come from runtime context rather than a stable shared service:

- the current user
- a route argument
- a config value
- a keyed service such as a specific cache, database connection, logger, mailer, or encrypter

Prefer normal dependency injection when you want the same service every time and there is no runtime context involved.

## How they are applied

When a parameter has a contextual attribute, the container lets that attribute supply the value before it falls back to the parameter's type-hint.

In practice, the order is:

1. a matching named argument you pass explicitly
2. the next positional argument you pass explicitly
3. a contextual attribute on the parameter
4. the class or interface type-hint
5. the parameter default value or `null`

## Built-in attributes

Fyre provides a set of built-in contextual attributes under `Fyre\Core\Attributes\*`.

### `Cache`

- **Use**: `#[Cache(string $key = CacheManager::DEFAULT)]`
- **Resolves**: `Cacher`

```php
use Fyre\Cache\Cacher;
use Fyre\Core\Attributes\Cache;

function cacheExample(#[Cache] Cacher $cacher): Cacher
{
    return $cacher;
}
```

### `Config`

- **Use**: `#[Config(string $key)]`
- **Resolves**: a config value

```php
use Fyre\Core\Attributes\Config;

function configExample(#[Config('App.name')] string|null $name): string|null
{
    return $name;
}
```

### `CurrentUser`

- **Use**: `#[CurrentUser]`
- **Resolves**: the current authenticated user, or `null`

```php
use Fyre\Core\Attributes\CurrentUser;
use Fyre\ORM\Entity;

function currentUserExample(#[CurrentUser] Entity|null $currentUser): Entity|null
{
    return $currentUser;
}
```

### `DB`

- **Use**: `#[DB(string $key = ConnectionManager::DEFAULT)]`
- **Resolves**: `Connection`

```php
use Fyre\Core\Attributes\DB;
use Fyre\DB\Connection;

function dbExample(#[DB] Connection $db): Connection
{
    return $db;
}
```

### `Encryption`

- **Use**: `#[Encryption(string $key = EncryptionManager::DEFAULT)]`
- **Resolves**: `Encrypter`

```php
use Fyre\Core\Attributes\Encryption;
use Fyre\Security\Encryption\Encrypter;

function encryptionExample(#[Encryption('openssl')] Encrypter $enc): Encrypter
{
    return $enc;
}
```

### `Log`

- **Use**: `#[Log(string $key = LogManager::DEFAULT)]`
- **Resolves**: `Logger`

```php
use Fyre\Core\Attributes\Log;
use Fyre\Log\Logger;

function logExample(#[Log('other')] Logger $log): Logger
{
    return $log;
}
```

### `Mail`

- **Use**: `#[Mail(string $key = MailManager::DEFAULT)]`
- **Resolves**: `Mailer`

```php
use Fyre\Core\Attributes\Mail;
use Fyre\Mail\Mailer;

function mailExample(#[Mail('other')] Mailer $mail): Mailer
{
    return $mail;
}
```

### `ORM`

- **Use**: `#[ORM(string $alias)]`
- **Resolves**: `Model`

```php
use Fyre\Core\Attributes\ORM;
use Fyre\ORM\Model;

function ormExample(#[ORM('Users')] Model $model): Model
{
    return $model;
}
```

### `RouteArgument`

- **Use**: `#[RouteArgument(string $name)]`
- **Resolves**: a route argument value, or `null` when missing

```php
use Fyre\Core\Attributes\RouteArgument;

function routeArgumentExample(#[RouteArgument('id')] int|null $routeId): int|null
{
    return $routeId;
}
```

## Custom contextual attributes

Create your own contextual attribute when you want a reusable parameter shortcut for runtime-specific data.

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

Use it anywhere the container is building or calling code:

```php
$file = $container->call(
    static fn(#[StorageFile('storage/example.txt')] File $f): File => $f
);
```

## Attribute handler overrides

Use `Container::bindAttribute()` when you want to override how an attribute resolves, especially in tests or custom runtimes.

```php
use Fyre\Core\Attributes\Config as ConfigAttribute;
use Fyre\Core\Container;

$container = new Container();

$container->bindAttribute(
    ConfigAttribute::class,
    static function(ConfigAttribute $attribute, Container $container): mixed {
        return $attribute->resolve($container);
    }
);
```

## Behavior notes

A few practical details are worth keeping in mind:

- If you pass a named or positional argument for the parameter, that value wins and the attribute is not used.
- If a parameter has a contextual attribute, the container uses it before trying the type-hint directly.
- Only the first matching contextual attribute on a parameter is used.

## Related

- [Container](container.md)
- [Helpers](helpers.md)
- [Auth](../auth/index.md)
- [Cache](../cache/index.md)
- [Config](config.md)
- [Database connections](../database/connections.md)
- [Encryption](../security/encryption.md)
- [Logging](../logging/index.md)
- [Mail](../mail/index.md)
- [ORM](../orm/index.md)
- [Route Bindings](../routing/route-bindings.md)
