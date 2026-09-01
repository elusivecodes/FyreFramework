# Macros

Macros let you add small convenience methods to macro-enabled classes at runtime.

Use them for small application-specific extensions that do not justify a subclass or wrapper.

## Table of Contents

- [Method guide](#method-guide)
  - [Instance macros](#instance-macros)
  - [Static macros](#static-macros)
- [Macro-enabled classes](#macro-enabled-classes)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Method guide

Register macros during application bootstrapping, before the first call. Use an instance macro when the callback needs `$this`; use a static macro for a class-level operation.

### Instance macros

#### **Register an instance macro** (`macro()`)

Adds a macro that can be called on an object instance. A registered `Closure` is bound to the target object and its class scope when invoked.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback.

```php
use Fyre\Utility\Formatter;

Formatter::macro('usd', function (float|int|string $value): string {
    return $this->currency($value, 'USD');
});

$formatter = app(Formatter::class);
$formatted = $formatter->usd(25);
```

#### **Check whether a macro exists** (`hasMacro()`)

Arguments:
- `$name` (`string`): macro name.

```php
if (Formatter::hasMacro('usd')) {
    // ...
}
```

#### **Clear instance macros** (`clearMacros()`)

Clears the instance macro registry for the class.

```php
Formatter::clearMacros();
```

### Static macros

#### **Register a static macro** (`staticMacro()`)

Adds a macro that can be called on the class. A registered `Closure` is bound to the class scope without an object when invoked.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback.

```php
use Fyre\Utility\Str;

Str::staticMacro('surround', static function (string $value, string $prefix, string $suffix): string {
    return $prefix.$value.$suffix;
});

$value = Str::surround('value', '[', ']');
```

#### **Check whether a static macro exists** (`hasStaticMacro()`)

Arguments:
- `$name` (`string`): macro name.

```php
if (Str::hasStaticMacro('surround')) {
    // ...
}
```

#### **Clear static macros** (`clearStaticMacros()`)

Clears the static macro registry for the class.

```php
Str::clearStaticMacros();
```

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
- [Language (Lang)](lang.md)
- [Loader](loader.md)
- [Router](../routing/router.md)
