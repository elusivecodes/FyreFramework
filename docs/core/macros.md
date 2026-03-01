# Macros

Macros let you extend classes at runtime by registering callbacks that behave like methods. Because macros are invoked via `__call()` / `__callStatic()`, they are only considered when the real method does not exist.

## Table of Contents

- [Purpose](#purpose)
- [How macros work](#how-macros-work)
  - [MacroTrait binding](#macrotrait-binding)
  - [StaticMacroTrait binding](#staticmacrotrait-binding)
  - [Choosing instance vs static macros](#choosing-instance-vs-static-macros)
  - [Macro registry behavior](#macro-registry-behavior)
- [Method guide](#method-guide)
  - [Instance macros](#instance-macros)
  - [Static macros](#static-macros)
- [Framework usage](#framework-usage)
  - [Common instance-macro targets](#common-instance-macro-targets)
  - [Common static-macro targets](#common-static-macro-targets)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Macros are a lightweight way to add convenience methods without subclassing or wrapping. They’re especially useful for small adapters and fluent helpers in application code.

## How macros work

### MacroTrait binding

`MacroTrait` registers macros via `macro()` and invokes them through `__call()` on the instance.

If the registered callback is a `Closure`, it is bound to:

- the instance (`$this`)
- the calling class scope (so the macro can access non-public members of that class)

If a `Closure` macro cannot be bound, the call throws `BadMethodCallException`.

### StaticMacroTrait binding

`StaticMacroTrait` registers macros via `staticMacro()` and invokes them through `__callStatic()` on the class.

If the registered callback is a `Closure`, it is bound to the calling class scope when invoked. That gives the macro access to non-public static members (and enables `self::...` inside the macro body).

If a `Closure` macro cannot be bound, the call throws `BadMethodCallException`.

### Choosing instance vs static macros

Choose based on what the “method” needs to act on:

- Use an instance macro (`MacroTrait`) when the macro needs instance state (for example, it reads `$this->...` or should behave like a per-object convenience method).
- Use a static macro (`StaticMacroTrait`) when the macro is purely class-level behavior (for example, helper methods that don’t depend on an object instance and can be called as `ClassName::method()`).

### Macro registry behavior

🧠 Both traits store macros in a protected static array, so the registry is shared across instances and (by default) across an inheritance chain:

- every instance of a class shares the same macro registry
- subclasses inherit the registry unless they redeclare the underlying static property
- `clearMacros()` / `clearStaticMacros()` clears the registry for the called class (and therefore affects subclasses that share the same static property)

## Method guide

This section focuses on the macro APIs you’ll use most.

Register macros during application bootstrapping (before first use). Registering a macro with an existing name overwrites the previous macro.

### Instance macros

#### **Register an instance macro** (`macro()`)

Registers a macro by name. The macro is invoked only when the real method does not exist.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback (a `Closure` is bound to the instance on call).

```php
use Fyre\Utility\Formatter;

Formatter::macro('usd', function (float|int|string $value): string {
    return $this->currency($value, 'USD');
});

function formatTotal(Formatter $formatter): string
{
    return $formatter->usd(19.95);
}
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

Clears the macro registry for the class (and any subclasses sharing the same static registry).

```php
Formatter::clearMacros();
```

### Static macros

#### **Register a static macro** (`staticMacro()`)

Registers a static macro by name. The macro is invoked only when the real static method does not exist.

Arguments:
- `$name` (`string`): macro name.
- `$macro` (`callable`): macro callback (a `Closure` is bound to class scope on call).

```php
use Fyre\Utility\Str;

Str::staticMacro('surround', function (string $value, string $prefix, string $suffix): string {
    return $prefix.$value.$suffix;
});

echo Str::surround('name', '[', ']');
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

Clears the static macro registry for the class (and any subclasses sharing the same static registry).

```php
Str::clearStaticMacros();
```

## Framework usage

Many framework classes include these traits, so they can be extended with macros the same way as the examples above (by calling `::macro()` / `::staticMacro()` on the class).

Rather than listing every macro-enabled class here, these are some of the more common ones you may want to extend:

### Common instance-macro targets

- Core: `Container`, `Config`, `Lang`, `Loader`
- HTTP: `Client`, `Request`, `Response`, `ServerRequest`
- Router: `Router`
- Utility: `Collection`, `Formatter`

### Common static-macro targets

- Utility: `Arr`, `Collection`, `Str`

If you want the full list for your version, search the source for `use MacroTrait;` or `use StaticMacroTrait;`.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Macros are invoked only when the method does not exist. Missing macros throw `BadMethodCallException`.
- Macro registries are static, so registering or clearing macros affects all instances that share the underlying static property (including subclasses).
- If a real method with the same name is added later, it takes precedence over the macro (the macro is no longer invoked).
- Macro registries persist for the lifetime of the PHP process, so remember to clear them in tests (for example, in `tearDown()`).
- `Closure` macros are bound to class scope, so they can access non-public members of the class. Treat macro code as part of the class’ trusted implementation.

## Related

- [Core](index.md)
- [Container](container.md)
- [Language (Lang)](lang.md)
- [Loader](loader.md)
- [Router](../routing/router.md)
- [Forge](../database/forge.md)
