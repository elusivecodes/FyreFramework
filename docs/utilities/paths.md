# Paths

`Fyre\Utility\Path` joins, normalizes, resolves, and inspects path strings without reading the filesystem.

Use [File System](file-system.md) when the operation should create, read, copy, move, or delete something on disk.

## Table of Contents

- [Common operations](#common-operations)
- [Method guide](#method-guide)
  - [Join, normalize, and resolve](#join-normalize-and-resolve)
  - [Inspect and format](#inspect-and-format)
- [Platform behavior](#platform-behavior)
- [Related](#related)

## Common operations

All methods are static and use `Path::SEPARATOR`, an alias of the current runtime's `DIRECTORY_SEPARATOR`:

```php
use Fyre\Utility\Path;

$cache = Path::join('tmp', 'cache');
$file = Path::join($cache, 'routes.php');
$normalized = Path::normalize('tmp/cache/../logs/');
```

On a Unix-like runtime the results are `tmp/cache`, `tmp/cache/routes.php`, and `tmp/logs/`. Equivalent output uses `\` on Windows.

## Method guide

The methods below use the imported `Path` class from [Common operations](#common-operations).

### Join, normalize, and resolve

| Method | Behavior |
| --- | --- |
| `join(string ...$paths): string` | discard empty-string segments, join with the platform separator, then normalize |
| `normalize(string $path = ''): string` | collapse empty, `.`, and resolvable `..` segments while preserving a trailing separator |
| `resolve(string ...$paths): string` | scan from the final segment backward and discard anything before the last absolute segment, then join and normalize |

`resolve()` is a string operation, not `realpath()`. Relative input remains relative rather than being prefixed with the current working directory. With no arguments it returns `getcwd()`, or `.` if the current directory is unavailable.

```php
Path::resolve('tmp', 'cache', '..', 'logs'); // "tmp/logs"
Path::resolve('ignored', '/var', 'log'); // "/var/log" on Unix-like systems
```

Normalization preserves leading `..` segments in a relative path and discards attempts to move above an absolute root. An empty normalized result is `.`, except an absolute root remains the platform separator.

### Inspect and format

| Method | Return behavior |
| --- | --- |
| `baseName(string $path): string` | `PATHINFO_BASENAME` |
| `dirName(string $path): string` | `PATHINFO_DIRNAME` |
| `extension(string $path): string` | `PATHINFO_EXTENSION` |
| `fileName(string $path): string` | `PATHINFO_FILENAME` |
| `isAbsolute(string $path): bool` | whether the path starts with the current platform separator or a Windows drive plus `\` or `/` |
| `parse(string $path): array` | native `pathinfo()` result |
| `format(array $pathInfo): string` | normalized join of the `dirname` and `basename` entries; missing entries become empty strings |

```php
$parts = Path::parse('tmp/cache/routes.php');

// [
//     'dirname' => 'tmp/cache',
//     'basename' => 'routes.php',
//     'extension' => 'php',
//     'filename' => 'routes',
// ]

Path::format($parts); // "tmp/cache/routes.php"
```

`parse()` retains PHP's `pathinfo()` platform and edge-case behavior, including its handling of dotfiles and multiple extensions.

## Platform behavior

- `normalize()` splits only on the current `DIRECTORY_SEPARATOR`; it does not translate arbitrary `/` and `\` characters between platforms.
- On Unix-like systems, a leading `/` is absolute. On Windows, a leading `\`, a drive path such as `C:\logs`, and a drive path using `/` are recognized.
- A Windows drive path is recognized by `isAbsolute()` even when the current runtime is not Windows, but normalization still uses the current platform separator.
- `join()` removes only `''`; `resolve()` skips all falsey segments, including the string `0`.
- `Path` supports static macros.

## Related

- [Utilities](index.md)
- [File System](file-system.md)
