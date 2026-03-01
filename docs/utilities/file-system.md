# File System

`File` (`Fyre\Utility\FileSystem\File`) and `Folder` (`Fyre\Utility\FileSystem\Folder`) are small object wrappers around common filesystem operations like reading, writing, copying, and directory management.

For path-only operations (join/normalize/resolve, basename/dirname, etc), see [Paths](paths.md).


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Folder](#folder)
  - [File](#file)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `File` and `Folder` when you want a small, fluent API for filesystem operations (existence checks, reading/writing, copying/moving, deleting). Both normalize the input path using `Path::resolve()` in the constructor.

📌 Path handling is platform-sensitive (separators, permissions, and case sensitivity). If you see differences between environments, review the [Paths behavior notes](paths.md#behavior-notes) first.

## Quick start

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

Copy while preserving permissions and timestamps:

```php
$file->copy('tmp/cache/routes.backup.json', false);
```

## Constants

`File` exposes constants that mirror PHP’s `flock()` operations:

- `File::LOCK_SHARED` (shared lock; `LOCK_SH`)
- `File::LOCK_EXCLUSIVE` (exclusive lock; `LOCK_EX`)
- `File::UNLOCK` (release lock; `LOCK_UN`)

## Method guide

### Folder

#### **Check whether a folder exists** (`exists()`)

Returns `true` when the resolved path exists and is a directory.

```php
$ok = $folder->exists();
```

#### **Create a folder** (`create()`)

Creates the directory (recursively) using the supplied permissions.

Arguments:
- `$permissions` (`int`): the directory permissions.

```php
$folder->create(0755);
```

#### **List direct children** (`contents()`)

Returns `File` and `Folder` objects for the folder’s direct children.

```php
$items = $folder->contents();
```

#### **Check whether a folder is empty** (`isEmpty()`)

Returns `true` when the directory has no children.

```php
$empty = $folder->isEmpty();
```

#### **Get the total size** (`size()`)

Returns the total size (in bytes) of the folder contents, recursively.

```php
$bytes = $folder->size();
```

#### **Copy a folder** (`copy()`)

Recursively copies the folder contents to a destination.

Arguments:
- `$destination` (`string`): the destination path.
- `$overwrite` (`bool`): whether to overwrite existing files.

```php
$folder->copy('tmp/cache.backup', false);
```

#### **Move a folder** (`move()`)

Moves the folder by copying to the destination and then deleting the original.

Arguments:
- `$destination` (`string`): the destination path.
- `$overwrite` (`bool`): whether to overwrite existing files.

```php
$moved = $folder->move('tmp/cache.old');
```

#### **Empty a folder** (`empty()`)

Deletes all children recursively, leaving the folder itself in place.

```php
$folder->empty();
```

#### **Delete a folder** (`delete()`)

Empties the folder and then removes the directory itself.

```php
$folder->delete();
```

#### **Get the resolved path** (`path()`)

Returns the resolved path for the folder.

```php
$path = $folder->path();
```

#### **Get the folder name** (`name()`)

Returns the last path segment.

```php
$name = $folder->name();
```

### File

#### **Check whether a file exists** (`exists()`)

Returns `true` when the resolved path exists and is a regular file.

```php
$ok = $file->exists();
```

#### **Read the full contents** (`contents()`)

Reads the full file into a string.

```php
$contents = $file->contents();
```

#### **Open a file handle** (`open()`)

Opens a file handle using a PHP `fopen()` mode string.

Arguments:
- `$mode` (`string`): the `fopen()` mode (for example: `'r'`, `'c+'`).

```php
$file->open('r');
```

#### **Close a file handle** (`close()`)

Closes the current file handle.

```php
$file->open('r')->close();
```

#### **Read from an open handle** (`read()`)

Reads a number of bytes from the current position.

Arguments:
- `$length` (`int`): the number of bytes to read.

```php
$file->open('r');
$chunk = $file->read(1024);
$file->close();
```

#### **Write to an open handle** (`write()`)

Writes a string to the file at the current position.

Arguments:
- `$data` (`string`): the data to write.

```php
$file = new File('tmp/cache/routes.json', true);
$file->open('c+')->truncate()->write("{}\n")->close();
```

#### **Truncate an open handle** (`truncate()`)

Truncates the file to a size (defaults to `0`).

Arguments:
- `$size` (`int`): the size to truncate to (in bytes).

```php
$file = new File('tmp/cache/routes.json', true);
$file->open('c+')->truncate()->close();
```

#### **Move the file pointer** (`seek()`)

Moves the pointer to an absolute byte offset.

Arguments:
- `$offset` (`int`): the new pointer position.

```php
$file->open('r')->seek(0)->close();
```

#### **Get the file pointer position** (`tell()`)

Returns the current pointer offset.

```php
$file->open('r');
$pos = $file->tell();
$file->close();
```

#### **Rewind the file pointer** (`rewind()`)

Rewinds the pointer to the beginning of the file.

```php
$file->open('r')->rewind()->close();
```

#### **Check for end-of-file** (`ended()`)

Returns `true` if the pointer is at EOF.

```php
$file->open('r');
$atEnd = $file->ended();
$file->close();
```

#### **Parse a CSV row** (`csv()`)

Reads and parses a CSV row from the current pointer position.

Arguments:
- `$length` (`int`): the maximum length to parse.
- `$separator` (`string`): the field separator.
- `$enclosure` (`string`): the field enclosure character.
- `$escape` (`string`): the escape character.

```php
$file = new File('tmp/cache/data.csv');
$file->open('r');
$row = $file->csv();
$file->close();
```

#### **Lock an open handle** (`lock()`)

Locks the open file handle using `flock()`.

Arguments:
- `$operation` (`int|null`): the lock operation (defaults to `File::LOCK_SHARED`).

```php
$file = new File('tmp/cache/routes.json', true);
$file->open('c+')->lock(File::LOCK_EXCLUSIVE)->write("{}\n")->unlock()->close();
```

#### **Unlock an open handle** (`unlock()`)

Releases a lock on the open handle.

```php
$file = new File('tmp/cache/routes.json', true);
$file->open('c+')->lock()->unlock()->close();
```

#### **Copy a file** (`copy()`)

Copies the file to a destination, optionally refusing to overwrite.

Arguments:
- `$destination` (`string`): the destination path.
- `$overwrite` (`bool`): whether to overwrite an existing destination file.

```php
$file->copy('tmp/cache/routes.backup.json', false);
```

#### **Delete a file** (`delete()`)

Deletes the file from disk.

```php
$file->delete();
```

#### **Create a file** (`create()`)

Creates the file (and its parent folder if needed).

```php
$file->create();
```

#### **Touch a file** (`touch()`)

Updates the modified time and access time, creating the file if needed.

Arguments:
- `$time` (`int|null`): the modified time (defaults to current time).
- `$accessTime` (`int|null`): the access time (defaults to `$time`).

```php
$file = new File('tmp/cache/routes.json', true);
$file->touch();
```

#### **Get the file size** (`size()`)

Returns the file size (in bytes).

```php
$bytes = $file->size();
```

#### **Get the MIME type** (`mimeType()`)

Returns a MIME type derived from file contents.

```php
$type = $file->mimeType();
```

#### **Check readability** (`isReadable()`)

Returns whether the current process can read the file.

```php
$ok = $file->isReadable();
```

#### **Check writability** (`isWritable()`)

Returns whether the current process can write to the file.

```php
$ok = $file->isWritable();
```

#### **Check executability** (`isExecutable()`)

Returns whether the file is executable.

```php
$file = new File('tmp/cache/script.sh');
$ok = $file->isExecutable();
```

#### **Read octal permissions** (`permissions()`)

Returns the file permissions as an octal string (for example: `'644'`).

```php
$perms = $file->permissions();
```

#### **Change file permissions** (`chmod()`)

Updates the file permissions.

Arguments:
- `$permissions` (`int`): the permissions.

```php
$file->chmod(0644);
```

#### **Get modified time** (`modifiedTime()`)

Returns the file modified time.

```php
$modified = $file->modifiedTime();
```

#### **Get access time** (`accessTime()`)

Returns the file access time.

```php
$accessed = $file->accessTime();
```

#### **Get file owner** (`owner()`)

Returns the file owner ID.

```php
$owner = $file->owner();
```

#### **Get file group** (`group()`)

Returns the file group ID.

```php
$group = $file->group();
```

#### **Get the resolved path** (`path()`)

Returns the resolved full path to the file.

```php
$path = $file->path();
```

#### **Get the parent folder** (`folder()`)

Returns the `Folder` for the file’s directory.

```php
$folder = $file->folder();
```

#### **Get the base name** (`baseName()`)

Returns the final path segment for the file.

```php
$name = $file->baseName();
```

#### **Get the directory name** (`dirName()`)

Returns the directory portion of the path.

```php
$dir = $file->dirName();
```

#### **Get the extension** (`extension()`)

Returns the file extension (without the dot).

```php
$ext = $file->extension();
```

#### **Get the file name** (`fileName()`)

Returns the filename without its extension.

```php
$name = $file->fileName();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `File` methods that operate on a handle (for example: `read()`, `write()`, `seek()`, `tell()`, `csv()`, `lock()`, and `truncate()`) require a prior `open()` call and throw when the handle is not valid.
- `File::lock()` defaults to a shared lock when `$operation` is `null` (use `File::LOCK_EXCLUSIVE` for an exclusive lock).
- `File::copy()` preserves permissions and access/modified times on the destination.
- `File::create()` creates the parent folder first when it does not already exist.
- `Folder::create()` throws when the directory already exists (it delegates to `mkdir()`).
- `Folder::move()` performs a copy-then-delete, and returns a new instance for the destination path.

## Related

- [Utilities](index.md)
- [Paths](paths.md)
