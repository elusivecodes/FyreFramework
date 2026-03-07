# Paths

`Path` (`Fyre\Utility\Path`) is a static utility class for joining, normalizing, resolving, and inspecting file paths.

For filesystem operations (reading, writing, copying), see [File System](file-system.md).


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Joining and normalizing](#joining-and-normalizing)
  - [Resolving](#resolving)
  - [Inspecting paths](#inspecting-paths)
  - [Parsing and formatting](#parsing-and-formatting)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `Path` when you want predictable, platform-aware path string handling without touching the filesystem. All operations are based on the current runtime `DIRECTORY_SEPARATOR`.

If you are working across operating systems (or running on Windows), review the [Behavior notes](#behavior-notes) for separator and absolute-path edge cases.

## Quick start

Joining and normalizing:

```php
use Fyre\Utility\Path;

$cacheDir = Path::join('tmp', 'cache');            // "tmp/cache"
$filePath = Path::join($cacheDir, 'routes.php');   // "tmp/cache/routes.php"
```

Resolving segments:

```php
$relative = Path::resolve('tmp', 'cache', 'views'); // "tmp/cache/views"
```

Parsing and formatting:

```php
$info = Path::parse('tmp/cache/routes.php');
$path = Path::format($info); // "tmp/cache/routes.php"
```

## Constants

`Path` exposes a small constant for working with separators:

- `Path::SEPARATOR` The current platform directory separator (alias of `DIRECTORY_SEPARATOR`).

## Method guide

Examples below assume `Path` is already imported.

### Joining and normalizing

#### **Join segments** (`join()`)

Joins path segments using the current platform separator and normalizes the result.

Arguments:
- `$paths` (`string ...`): the path segments.

```php
$path = Path::join('tmp', 'cache', 'routes.php'); // "tmp/cache/routes.php"
```

`join()` filters out empty segments before joining.

#### **Normalize a path string** (`normalize()`)

Normalizes a path string by collapsing `.` / `..` segments and duplicate separators.

Arguments:
- `$path` (`string`): the file path.

```php
$path = Path::normalize('tmp//cache/./../cache/routes.php'); // "tmp/cache/routes.php"
```

`normalize()` is a string operation; it does not check the filesystem.

### Resolving

#### **Resolve segments** (`resolve()`)

Resolves one or more segments into a normalized path. If an absolute segment is encountered, earlier segments are ignored.

Arguments:
- `$paths` (`string ...`): the path segments.

```php
$relative = Path::resolve('tmp', 'cache', 'views');  // "tmp/cache/views"
$absolute = Path::resolve('/var', 'log', 'app.log'); // "/var/log/app.log" (on Unix-like systems)
```

When called with no arguments, `resolve()` returns the current working directory (or `.` if it can’t be determined):

```php
$cwd = Path::resolve();
```

### Inspecting paths

`Path` includes small wrappers around `pathinfo()` for common pieces:

```php
$baseName = Path::baseName('tmp/cache/routes.php'); // routes.php
$dirName = Path::dirName('tmp/cache/routes.php');   // tmp/cache
$extension = Path::extension('tmp/cache/routes.php'); // php
$fileName = Path::fileName('tmp/cache/routes.php');   // routes
```

#### **Get the base name** (`baseName()`)

Returns the base name from a file path.

Arguments:
- `$path` (`string`): the file path.

```php
$baseName = Path::baseName('tmp/cache/routes.php'); // "routes.php"
```

#### **Get the directory name** (`dirName()`)

Returns the directory name from a file path.

Arguments:
- `$path` (`string`): the file path.

```php
$dirName = Path::dirName('tmp/cache/routes.php'); // "tmp/cache"
```

#### **Get the file extension** (`extension()`)

Returns the file extension from a file path.

Arguments:
- `$path` (`string`): the file path.

```php
$extension = Path::extension('tmp/cache/routes.php'); // "php"
```

#### **Get the file name** (`fileName()`)

Returns the file name (without extension) from a file path.

Arguments:
- `$path` (`string`): the file path.

```php
$fileName = Path::fileName('tmp/cache/routes.php'); // "routes"
```

#### **Check for an absolute path** (`isAbsolute()`)

Checks whether a path is absolute on the current platform. This includes Unix-style root paths and Windows drive-letter paths.

Arguments:
- `$path` (`string`): the file path.

```php
$ok = Path::isAbsolute('/var/log'); // true (on Unix-like systems)
$windows = Path::isAbsolute('C:\logs'); // true
```

### Parsing and formatting

#### **Parse a path** (`parse()`)

Parses a path using `pathinfo()`.

Arguments:
- `$path` (`string`): the file path.

```php
$info = Path::parse('tmp/cache/routes.php');
```

#### **Format a path** (`format()`)

Formats a `pathinfo()`-style array back into a path by joining `dirname` and `basename`.

Arguments:
- `$pathInfo` (`array<string, mixed>`): a path info array (commonly from `parse()`).

```php
$info = Path::parse('tmp/cache/routes.php');
$path = Path::format($info); // "tmp/cache/routes.php"
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `Path` uses the current platform separator (`DIRECTORY_SEPARATOR`, exposed as `Path::SEPARATOR`) for splitting and joining.
- `normalize()` returns `.` for an empty result. If the original path started with a directory separator and normalization removes all segments, it returns a single directory separator.
- `join()` filters out empty segments before joining; if all segments are empty, it returns `.`.
- `resolve()` is not `realpath()`; it does not check the filesystem.
- `isAbsolute()` treats both leading-separator paths and Windows drive-letter paths (for example `C:\path`) as absolute.

## Related

- [Utilities](index.md)
- [File System](file-system.md)
