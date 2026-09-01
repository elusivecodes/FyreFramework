# Contextual attributes

Contextual attributes tell the container to inject a value from the current runtime context, such as the current user, a route argument, a config value, or a keyed service.

## Table of Contents

- [Start here](#start-here)
- [Resolution order](#resolution-order)
- [Built-in attributes](#built-in-attributes)
- [Custom contextual attributes](#custom-contextual-attributes)
- [Override attribute handlers](#override-attribute-handlers)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Contextual attributes are resolved only while the container is building an object with `Container::build()` or invoking code with `Container::call()`. PHP does not resolve them when you instantiate a class or call a function directly.

Use them when a parameter depends on the current application context. Continue to use normal type-hinted dependency injection for stable services that do not need a key or request-specific value.

```php
use Fyre\Cache\Cacher;
use Fyre\Core\Attributes\Cache;
use Fyre\Core\Attributes\Config;
use Fyre\Core\Attributes\CurrentUser;
use Fyre\ORM\Entity;

final class ReportService
{
    public function __construct(
        #[Cache('reports')]
        protected Cacher $cache,
        #[Config('App.name')]
        protected string|null $appName,
        #[CurrentUser]
        protected Entity|null $currentUser
    ) {}
}

$service = $container->build(ReportService::class);
```

## Resolution order

For each parameter, the container uses the first available value in this order:

1. a matching named argument supplied to `build()` or `call()`
2. the next supplied positional argument
3. the first contextual attribute on the parameter
4. the class or interface type-hint
5. the parameter default value or `null`

An explicit argument therefore overrides the attribute. Once an attribute resolves—even to `null`—the container does not continue to the type-hint or parameter default.

## Built-in attributes

The built-in parameter attributes live under `Fyre\Core\Attributes`:

| Attribute | Resolves |
| --- | --- |
| `#[Cache(string $key = CacheManager::DEFAULT)]` | configured `Cacher` from `CacheManager` |
| `#[Config(string $key)]` | value returned by `Config::get($key)` |
| `#[CurrentUser]` | current authenticated `Entity`, or `null` |
| `#[DB(string $key = ConnectionManager::DEFAULT)]` | configured database `Connection` |
| `#[Encryption(string $key = EncryptionManager::DEFAULT)]` | configured `Encrypter` |
| `#[Log(string $key = LogManager::DEFAULT)]` | configured `Logger` |
| `#[Mail(string $key = MailManager::DEFAULT)]` | configured `Mailer` |
| `#[ORM(string $alias)]` | shared ORM `Model` for the alias |
| `#[RouteArgument(string $name)]` | value from the current request's `routeArguments`, or `null` |

The keyed attributes use their manager's `default` key when the key is optional. Pass a key or alias when you need a non-default service:

```php
use Fyre\Core\Attributes\DB;
use Fyre\Core\Attributes\Encryption;
use Fyre\Core\Attributes\ORM;
use Fyre\DB\Connection;
use Fyre\ORM\Model;
use Fyre\Security\Encryption\Encrypter;

final class AuditService
{
    public function __construct(
        #[DB('audit')]
        protected Connection $db,
        #[Encryption('archive')]
        protected Encrypter $encrypter,
        #[ORM('Users')]
        protected Model $users
    ) {}
}
```

`RouteArgument` is useful when the parameter name differs from the route key or when code should state the source explicitly. It reads the current `routeArguments` value, including a value already replaced by route binding middleware.

## Custom contextual attributes

Extend `ContextualAttribute` when an application-specific value should be resolved the same way in several constructors or callbacks:

```php
use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Utility\FileSystem\File;
use Override;

/**
 * @extends ContextualAttribute<File>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class StorageFile extends ContextualAttribute
{
    public function __construct(
        protected string $path
    ) {}

    #[Override]
    public function resolve(Container $container): File
    {
        return $container->build(File::class, [
            'path' => $this->path,
            'create' => false,
        ]);
    }
}
```

The container instantiates the attribute, then calls `resolve()`:

```php
$file = $container->call(
    static fn(#[StorageFile('storage/example.txt')] File $file): File => $file
);
```

## Override attribute handlers

Use `Container::bindAttribute()` to replace an attribute's normal resolution. This is useful in tests and runtimes that need a different source for contextual state:

```php
use Fyre\Core\Attributes\CurrentUser;
use Fyre\ORM\Entity;

$container->bindAttribute(
    CurrentUser::class,
    static fn(): Entity => $testUser
);
```

The replacement is invoked through `Container::call()`. If it needs the instantiated attribute, declare a parameter named `$attribute`, optionally type-hinted with the attribute class.

## Behavior notes

- Only the first contextual attribute on a parameter is used.
- Attribute construction and resolution exceptions propagate to the caller.
- `RouteArgument` reads the current scoped `ServerRequest`; it returns `null` when the `routeArguments` attribute or requested key is missing.
- A handler registered with `bindAttribute()` applies whenever the container encounters that exact attribute class.

## Related

- [Container](container.md)
- [Helpers](helpers.md)
- [Authentication](../auth/authentication.md)
- [Cache](../cache/index.md)
- [Config](config.md)
- [Database connections](../database/connections.md)
- [Encryption](../security/encryption.md)
- [Logging](../logging/index.md)
- [Mail](../mail/index.md)
- [ORM](../orm/index.md)
- [Route Bindings](../routing/route-bindings.md)
