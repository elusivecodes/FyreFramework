# Debugging

Use `DebugTrait` when you want safer, more readable debug output from your own classes.

It gives `var_dump()` and tools that respect `__debugInfo()` a structured view of the object while letting you mask sensitive values.

## Table of Contents

- [Start here](#start-here)
- [Masking sensitive values](#masking-sensitive-values)
  - [Mask an entire property](#mask-an-entire-property)
  - [Mask nested array keys](#mask-nested-array-keys)
- [How values are displayed](#how-values-are-displayed)
- [Method guide](#method-guide)
- [Behavior notes](#behavior-notes)
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

That gives the class a `__debugInfo()` implementation so debuggers and `var_dump()` can show useful data without dumping every raw property blindly.

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

## How values are displayed

`DebugTrait` keeps debug output readable by applying a few rules:

- scalar values and `null` are left as they are
- masked values become `[*****]`
- arrays are expanded only to a limited depth
- non-scalar objects and resources are reduced to a type label such as `[stdClass]`

## Method guide

#### **Get debug info** (`__debugInfo()`)

Returns the structured array used for debug output.

```php
use Fyre\Utility\FileSystem\File;

$info = (new File('/path/to/file.txt'))->__debugInfo();
```

## Behavior notes

A few practical details are worth keeping in mind:

- `__debugInfo()` includes only properties visible to the current scope.
- Uninitialized typed properties are skipped.
- Arrays deeper than the maximum debug depth are collapsed before nested-key masking can continue.
- If you use `DebugTrait`, treat the output as potentially user-visible and mask secrets by default.

## Related

- [Core](index.md)
- [Helpers](helpers.md)
