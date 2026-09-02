# Macros

Macros let you add small convenience methods to macro-enabled classes at runtime.

Use them for small application-specific extensions that do not justify a subclass or wrapper.

## Table of Contents

- [Register instance macros](#register-instance-macros)
- [Register static macros](#register-static-macros)
- [Macro-enabled classes](#macro-enabled-classes)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Register instance macros

Register macros during application bootstrapping, before the first call. Use `macro(string $name, callable $macro): void` when the callback should be called on an object instance. A `Closure` is bound to that object and its class scope, so it can use `$this`:

```php
use Fyre\Utility\Formatter;

Formatter::macro('usd', function(float|int|string $value): string {
    return $this->currency($value, 'USD');
});

$formatter = app(Formatter::class);
$formatted = $formatter->usd(25);
```

Use `hasMacro(string $name): bool` to check a registration and `clearMacros(): void` to clear every instance macro registered for the class.

## Register static macros

Use `staticMacro(string $name, callable $macro): void` when the callback should be called on the class. A `Closure` is bound to the class scope without an object:

```php
use Fyre\Utility\Str;

Str::staticMacro('surround', static function(string $value, string $prefix, string $suffix): string {
    return $prefix.$value.$suffix;
});

$value = Str::surround('value', '[', ']');
```

Use `hasStaticMacro(string $name): bool` to check a registration and `clearStaticMacros(): void` to clear every static macro registered for the class.

## Macro-enabled classes

Examples include:

- **Core**: `Container`, `Config`, `Lang`, and `Loader`
- **HTTP**: `Client`, `Client\Request`, `ClientResponse`, `ServerRequest`, `Stream`, and `Uri`
- **Database and ORM**: connections, queries, result sets, models, and entities
- **Routing**: `Router` and route classes
- **Utilities**: `Arr`, `Collection`, `DateTime`, `Formatter`, `Image`, `Path`, and `Str`

Support is supplied by `MacroTrait` for instance macros and `StaticMacroTrait` for static macros. Some classes use both.

## Behavior notes

- An accessible declared method takes precedence; macro lookup occurs through `__call()` or `__callStatic()` for otherwise inaccessible calls.
- Registries are static, so registrations affect every instance that shares the registry in the current PHP process.
- Registering the same name again replaces the previous callback.
- A missing macro, or a `Closure` that cannot be bound to the required scope, causes a `BadMethodCallException`.
- Macro registrations persist for the lifetime of the process. Clear them between tests when state could leak across cases.

## Related

- [Core](index.md)
- [Container](container.md)
- [Language (`Lang`)](lang.md)
- [Loader](loader.md)
- [Router](../routing/router.md)
