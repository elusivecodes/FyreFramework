# Debugging

Use `DebugTrait` when you want safer, more readable debug output from your own classes.

It gives `var_dump()` and tools that respect `__debugInfo()` a structured view of the object while letting you mask sensitive values.

## Table of Contents

- [Start here](#start-here)
- [Masking sensitive values](#masking-sensitive-values)
  - [Mask an entire property](#mask-an-entire-property)
  - [Mask nested array keys](#mask-nested-array-keys)
- [Debug output](#debug-output)
- [Related](#related)

## Start here

Add `DebugTrait` to a class when you want structured debug output:

```php
use Fyre\Core\Traits\DebugTrait;

class Job
{
    use DebugTrait;

    public function __construct(
        protected string $id
    ) {}
}
```

The trait supplies `__debugInfo()`, which is used by `var_dump()` and other tools that respect PHP's debug representation.

## Masking sensitive values

### Mask an entire property

Use `SensitiveProperty` when the whole property should be hidden:

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

### Mask nested array keys

Use `SensitivePropertyArray` when only specific keys inside an array should be hidden:

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

## Debug output

Calling `__debugInfo()` returns a structured array containing the class name and initialized properties visible from the class's scope.

| Value | Debug representation |
| --- | --- |
| a masked non-empty value | `[*****]` |
| a scalar, `null`, or an empty string | the original value |
| an array within the depth limit | a recursively processed array |
| an array beyond `DEBUG_MAX_DEPTH` (default `3`) | `[...]` |
| an object or resource | its debug type, such as `[stdClass]` |

Uninitialized typed properties are skipped. Once an array reaches the depth limit it is collapsed as a whole, so nested masking rules are not applied below that point.

Treat debug output as potentially visible outside local development. Mark secrets explicitly rather than relying on their current value or nesting depth.

## Related

- [Core](index.md)
- [Helpers](helpers.md)
