# Debugging

`DebugTrait` provides predictable, safe debug output by masking sensitive values while still showing enough structure to understand what an object contains. Across the framework, many classes use `DebugTrait` as the common mechanism for safe `__debugInfo()` output.

## Table of Contents

- [Purpose](#purpose)
- [Safe debug output](#safe-debug-output)
  - [Masking with attributes](#masking-with-attributes)
  - [Depth and type normalization](#depth-and-type-normalization)
- [Usage patterns](#usage-patterns)
  - [Add `DebugTrait` to a class](#add-debugtrait-to-a-class)
  - [Mask secrets](#mask-secrets)
  - [Mask nested keys in arrays](#mask-nested-keys-in-arrays)
- [Method guide](#method-guide)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Debug output should be useful and safe. `DebugTrait` implements `__debugInfo()` so common tools like `var_dump()` can show structured information without accidentally leaking secrets.

## Safe debug output

`DebugTrait` implements `__debugInfo()` so an object can expose structured debug data while masking secrets. The returned debug array includes a `[class]` key and the object’s visible properties (properties are sorted by key; `[class]` is always first).

### Masking with attributes

Masking is opt-in and driven by attributes on properties. The trait looks for attributes that extend `SensitiveProperty`, which is why both `SensitiveProperty` and `SensitivePropertyArray` participate in masking.

- `SensitiveProperty` masks the entire property value.
- `SensitivePropertyArray` masks selected nested keys within an array property.

For masked keys, values are replaced with `[*****]` unless the original value is `null` or an empty string (`''`).
If the value is `null` or `''`, masking does not change it.

`SensitivePropertyArray` accepts a nested key structure:

- list form: `['password', 'token']`
- nested form: `['headers' => ['Authorization', 'Cookie']]`

### Depth and type normalization

`__debugInfo()` normalizes values to keep debug output readable:

- arrays are expanded until the maximum depth is reached (`DEBUG_MAX_DEPTH = 3`); deeper arrays become `[...]`
- scalars and `null` are kept as-is
- non-scalar non-array values (objects/resources) become their debug type (e.g. `[stdClass]`)

This page is about object-level debug representation. Helper functions such as `dump()` and `dd()` are separate conveniences that can display values, but `DebugTrait` controls what an object exposes through `__debugInfo()`.

## Usage patterns

These patterns show how to enable structured debug output and how to mask secrets intentionally.

### Add `DebugTrait` to a class

Add `DebugTrait` to enable structured debug output in tools like `var_dump()` (and debuggers that respect `__debugInfo()`).

```php
use Fyre\Core\Traits\DebugTrait;

class Job
{
    use DebugTrait;

    public function __construct(
        protected string $id
    ) {}
}

var_dump(new Job('abc'));
```

### Mask secrets

Use `SensitiveProperty` to mask an entire property value.

```php
use Fyre\Core\Attributes\SensitiveProperty;
use Fyre\Core\Traits\DebugTrait;

class Token
{
    use DebugTrait;

    public function __construct(
        #[SensitiveProperty]
        protected string $value
    ) {}
}
```

### Mask nested keys in arrays

Use `SensitivePropertyArray` to mask selected keys inside an array property.

```php
use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\Core\Traits\DebugTrait;

class ConnectionConfig
{
    use DebugTrait;

    public function __construct(
        #[SensitivePropertyArray(['password', 'headers' => ['Authorization']])]
        protected array $options
    ) {}
}
```

## Method guide

#### **Get debug info** (`__debugInfo()`)

Returns a structured array representation of the object intended for safe debug output.

```php
use Fyre\Core\Traits\DebugTrait;

class Job
{
    use DebugTrait;

    public function __construct(
        protected string $id
    ) {}
}

$info = (new Job('abc'))->__debugInfo();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `__debugInfo()` includes only properties visible to the current scope (as per `get_object_vars()`) and skips uninitialized typed properties.
- Masking is applied only to keys declared via attributes. Arrays beyond the max depth are collapsed to `[...]` before nested keys can be inspected.
- Non-array objects and other non-scalar values are reduced to debug-type strings such as `[Foo\Bar]`.
- If you use `DebugTrait`, treat debug output as potentially user-visible (logs, error pages) and mask secrets by default.

## Related

- [Core](index.md)
- [Macros](macros.md)
