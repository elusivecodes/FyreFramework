# File System

`Fyre\Utility\FileSystem\File` and `Folder` provide object APIs for common filesystem operations. Both resolve their constructor path with [`Path::resolve()`](paths.md#join-normalize-and-resolve).

These objects describe one path; operations act on the filesystem immediately and throw when the underlying PHP operation fails.

## Table of Contents

- [Create file and folder objects](#create-file-and-folder-objects)
- [Read and write a file](#read-and-write-a-file)
- [Method guide](#method-guide)
  - [`File`](#file)
  - [`Folder`](#folder)
- [Mutation and platform behavior](#mutation-and-platform-behavior)
- [Related](#related)

## Create file and folder objects

Pass `true` to create a missing target during construction:

```php
use Fyre\Utility\FileSystem\File;
use Fyre\Utility\FileSystem\Folder;

$folder = new Folder('tmp/cache', true, 0755);
$file = new File('tmp/cache/routes.json', true);
```

`File` creates a missing parent folder before creating the file. `Folder` creates parent directories recursively. Calling `create()` directly when the target already exists throws.

## Read and write a file

`contents()` reads a complete file without retaining a handle. Incremental operations require `open()` and operate on the stored handle until `close()`:

```php
$file
    ->open('c+')
    ->lock(File::LOCK_EXCLUSIVE)
    ->truncate()
    ->write("{\"routes\":[]}\n")
    ->rewind();

$contents = $file->read(14);

$file
    ->unlock()
    ->close();
```

`write()` loops until all bytes are written. The object does not close an open handle automatically as part of these method chains.

## Method guide

The methods below use the objects created above.

### `File`

#### **Manage complete files**

| Method | Side effect or result |
| --- | --- |
| `create(): static` | create the file and any missing parent folder |
| `contents(): string` | read all bytes without using the stored handle |
| `copy(string $destination, bool $overwrite = true): static` | copy to an existing parent directory and preserve permissions and timestamps; return the source object |
| `delete(): static` | unlink the file |
| `touch(int\|null $time = null, int\|null $accessTime = null): static` | set timestamps, defaulting both to the current time |
| `chmod(int $permissions): static` | change permissions |

`copy()` overwrites by default. It does not create the destination's parent folder.

#### **Use an open handle**

| Method | Behavior |
| --- | --- |
| `open(string $mode = 'r'): static` | open and retain a PHP stream using the supplied native mode |
| `close(): static` | close the retained handle |
| `read(int $length): string` | read up to a positive byte length |
| `write(string $data): static` | write every byte or throw |
| `truncate(int $size = 0): static` | truncate to a non-negative byte size |
| `seek(int $offset): static` | seek from the beginning |
| `tell(): int` | current byte offset |
| `rewind(): static` | move to offset `0` |
| `ended(): bool` | whether the end-of-file indicator is set |
| `csv(int $length = 0, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array` | parse the next CSV row; a negative length throws |
| `lock(int\|null $operation = null): static` | acquire an advisory lock; `null` means `LOCK_SHARED` |
| `unlock(): static` | release the advisory lock |

Every method in this table requires a valid stored handle except `open()`. Reaching EOF in `csv()` is treated as a failed read and throws rather than returning `false`.

Lock constants mirror PHP's `flock()` values: `LOCK_SHARED`, `LOCK_EXCLUSIVE`, and `UNLOCK`.

#### **Inspect a file**

| Method | Return |
| --- | --- |
| `exists()` | whether the path is an existing regular file |
| `isReadable()`, `isWritable()`, `isExecutable()` | native permission checks |
| `size()` | bytes |
| `mimeType()` | MIME type without parameters, falling back to `application/octet-stream` |
| `permissions()` | octal permission string without a leading `0` or fixed-width padding |
| `modifiedTime()`, `accessTime()` | Unix timestamps |
| `owner()`, `group()` | numeric owner and group IDs |
| `path()` | resolved path |
| `folder()` | the associated `Folder` object |
| `baseName()`, `dirName()`, `extension()`, `fileName()` | path components |

### `Folder`

| Method | Side effect or result |
| --- | --- |
| `create(int $permissions = 0755): static` | recursively create the folder |
| `contents(): array` | direct children as `File` and `Folder` objects; order follows `FilesystemIterator` |
| `isEmpty(): bool` | whether there are no direct children |
| `size(): int` | recursive total of iterator entry sizes |
| `copy(string $destination, bool $overwrite = true): static` | recursively copy, create the destination, preserve file permissions/timestamps, and return the source object |
| `move(string $destination, bool $overwrite = true): static` | copy, delete the source tree, and return a new destination object |
| `empty(): static` | recursively remove every child while retaining the folder |
| `delete(): static` | empty and remove the folder |
| `exists(): bool` | whether the path is an existing directory |
| `path(): string` | resolved path |
| `name(): string` | final path segment, or the platform separator for a root |

`copy()` and `move()` overwrite colliding files by default. Pass `false` to fail when a destination file already exists.

## Mutation and platform behavior

- `create()`, `copy()`, `move()`, `empty()`, and `delete()` change the filesystem immediately. `empty()` and `delete()` are recursive and are not recoverable through these APIs.
- File locks use `flock()` and therefore retain its advisory and platform-specific semantics.
- Permission, owner, group, executable-bit, MIME, timestamp, and directory-size results depend on the host filesystem and PHP runtime.
- `mimeType()` requires the PHP Fileinfo extension.
- `File::copy()` and `Folder::copy()` preserve metadata on copied files, but folder metadata is not reapplied after creation.
- Methods wrap failed native operations in `ErrorException` or `RuntimeException` rather than returning `false`.
- `File` and `Folder` support instance macros.

## Related

- [Utilities](index.md)
- [Paths](paths.md)
- [Images](image.md)
