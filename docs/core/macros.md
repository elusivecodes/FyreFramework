# Macros

Macros let you add small convenience methods to macro-enabled classes at runtime.

They are a good fit for lightweight application helpers when subclassing or wrapping a class would be more work than the feature needs.

## Table of Contents

- [Start here](#start-here)
- [Choosing instance vs static macros](#choosing-instance-vs-static-macros)
- [Common macro targets](#common-macro-targets)
- [Method guide](#method-guide)
  - [Instance macros](#instance-macros)
  - [Static macros](#static-macros)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Register macros during application bootstrap, then call them like normal methods.

Instance macros are useful when the macro should act on an object:

```php
use Fyre\Utility\Formatter;

Formatter::macro('usd', function (float|int|string $value): string {
    return $this->currency($value, 'USD');
});
```

Static macros are useful when the macro should act like a class-level helper:

```php
use Fyre\Utility\Str;

Str::staticMacro('surround', static function (string $value, string $prefix, string $suffix): string {
    return $prefix.$value.$suffix;
});
```

## Choosing instance vs static macros

- Use an **instance macro** when the macro needs `$this` or should feel like an object method.
- Use a **static macro** when the macro should be called on the class itself.

When you register a macro with a `Closure`, Fyre binds it so it can behave like a normal method for that class.

## Common macro targets

Common macro-enabled classes include:

- **Core**: `Container`, `Config`, `Lang`, `Loader`
- **HTTP**: `Client`, `Request`, `Response`, `ServerRequest`
- **Routing**: `Router`
- **Utilities**: `Arr`, `Collection`, `Formatter`, `Str`

If you want the full list for your version, search the source for `use MacroTrait;` or `use StaticMacroTrait;`.

## Method guide

Register macros before first use. Registering a macro with an existing name overwrites the previous one.

### Instance macros

#### **Register an instance macro** (`macro()`)

Adds a macro that can be called on an object instance.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback.

```php
use Fyre\Utility\Formatter;

Formatter::macro('usd', function (float|int|string $value): string {
    return $this->currency($value, 'USD');
});
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

Adds a macro that can be called on the class.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback.

```php
use Fyre\Utility\Str;

Str::staticMacro('surround', static function (string $value, string $prefix, string $suffix): string {
    return $prefix.$value.$suffix;
});
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

## Behavior notes

A few practical details are worth keeping in mind:

- Macros are used only when the real method does not exist.
- Macro registries are static, so changes affect all instances of that class in the current PHP process.
- Registering a macro with an existing name overwrites the previous one.
- Macro registrations persist for the lifetime of the process, so clear them in tests when needed.

## Related

- [Core](index.md)
- [Container](container.md)
- [Language (Lang)](lang.md)
- [Loader](loader.md)
- [Router](../routing/router.md)
