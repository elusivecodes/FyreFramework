# File System

Use `File` and `Folder` when you want a small object API for common filesystem operations like reading, writing, copying, moving, and directory management.

For path-only operations (join/normalize/resolve, basename/dirname, etc), see [Paths](paths.md).

## Table of Contents

- [Common operations](#common-operations)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Folder operations](#folder-operations)
  - [File contents and handles](#file-contents-and-handles)
  - [File management and metadata](#file-management-and-metadata)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Common operations

Both classes normalize the input path using `Path::resolve()` in the constructor.

Path handling is platform-sensitive. If you see differences between environments, review the [Paths behavior notes](paths.md#behavior-notes) first.

Creating instances (optionally creating the target on disk):

```php
use Fyre\Utility\FileSystem\File;
use Fyre\Utility\FileSystem\Folder;

$folder = new Folder('tmp/cache', true);
$file = new File('tmp/cache/routes.json', true);
```

List a folder’s direct children:

```php
$folder = new Folder('tmp/cache', true);

$isEmpty = $folder->isEmpty();
$size = $folder->size();
$contents = $folder->contents();
```

`Folder::contents()` returns a list of `File` and `Folder` objects for the direct children of the folder.

Read an entire file:

```php
$file = new File('tmp/cache/routes.json');

if ($file->exists()) {
    $contents = $file->contents();
}
```

Write using an explicit handle:

```php
$file = new File('tmp/cache/routes.json', true);
$file
    ->open('c+')
    ->truncate(0)
    ->write("{\"routes\":[]}\n")
    ->close();
```

## Constants

`File` exposes constants that mirror PHP’s `flock()` operations:

- `File::LOCK_SHARED` (shared lock; `LOCK_SH`)
- `File::LOCK_EXCLUSIVE` (exclusive lock; `LOCK_EX`)
- `File::UNLOCK` (release lock; `LOCK_UN`)

## Method guide

### Folder operations

| Method | Behavior |
| --- | --- |
| `exists()` | checks whether the path exists and is a directory |
| `create($permissions = 0755)` | creates the directory |
| `contents()` | returns the direct children as `File` and `Folder` objects |
| `isEmpty()` | checks for direct children |
| `size()` | returns the recursive filesystem size |
| `copy($destination, $overwrite = true)` | recursively copies the folder |
| `move($destination, $overwrite = true)` | copies, deletes the source, and returns the destination instance |
| `empty()` | removes the folder's contents |
| `delete()` | removes the folder and its contents |
| `path()` / `name()` | return the resolved path or final path segment |

### File contents and handles

`contents()` reads the complete file without opening a persistent handle. For incremental I/O, call `open()`, perform one or more handle operations, and finish with `close()`.

| Method | Behavior |
| --- | --- |
| `open($mode = 'r')` / `close()` | open or close the stored file handle |
| `read($length)` / `write($data)` | read or write through the open handle |
| `truncate($size = 0)` | truncate the open file |
| `seek($offset)`, `tell()`, `rewind()` | move or inspect the file pointer |
| `ended()` | check whether the pointer has reached end-of-file |
| `csv($length = 0, $separator = ',', $enclosure = '"', $escape = '\\')` | parse the next CSV row |
| `lock($operation = null)` / `unlock()` | acquire or release an advisory file lock |

Mutating handle methods return the `File` instance so they can be chained:

```php
$file
    ->open('c+')
    ->lock(File::LOCK_EXCLUSIVE)
    ->truncate()
    ->write($contents)
    ->unlock()
    ->close();
```

### File management and metadata

| Method | Behavior |
| --- | --- |
| `create()` | creates the file and any missing parent directory |
| `copy($destination, $overwrite = true)` | copies the file and preserves its permissions and timestamps |
| `delete()` | removes the file |
| `touch($time = null, $accessTime = null)` | updates its timestamps |
| `chmod($permissions)` | changes its permissions |
| `exists()`, `isReadable()`, `isWritable()`, `isExecutable()` | inspect file state |
| `size()`, `mimeType()`, `permissions()` | return file metadata |
| `modifiedTime()`, `accessTime()`, `owner()`, `group()` | return timestamps and ownership |
| `path()`, `folder()`, `baseName()`, `dirName()`, `extension()`, `fileName()` | return path components |

## Behavior notes

- `File` methods that operate on a handle (for example: `read()`, `write()`, `seek()`, `tell()`, `csv()`, `lock()`, and `truncate()`) require a prior `open()` call and throw when the handle is not valid.
- `File::lock()` defaults to a shared lock when `$operation` is `null` (use `File::LOCK_EXCLUSIVE` for an exclusive lock).
- `File::copy()` preserves permissions and access/modified times on the destination.
- `File::create()` creates the parent folder first when it does not already exist.
- `Folder::create()` throws when the directory already exists (it delegates to `mkdir()`).
- `Folder::move()` performs a copy-then-delete, and returns a new instance for the destination path.

## Related

- [Utilities](index.md)
- [Paths](paths.md)
- [Images](image.md)
