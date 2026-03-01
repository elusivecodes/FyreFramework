<?php
declare(strict_types=1);

namespace Fyre\Utility\FileSystem;

use finfo;
use Fyre\Core\Exceptions\ErrorException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use RuntimeException;

use function assert;
use function chmod;
use function copy;
use function decoct;
use function fclose;
use function feof;
use function fgetcsv;
use function file_exists;
use function file_get_contents;
use function fileatime;
use function filegroup;
use function filemtime;
use function fileowner;
use function fileperms;
use function filesize;
use function flock;
use function fopen;
use function fread;
use function fseek;
use function ftell;
use function ftruncate;
use function fwrite;
use function is_executable;
use function is_file;
use function is_readable;
use function is_resource;
use function is_writable;
use function rewind;
use function sprintf;
use function strtok;
use function time;
use function touch;
use function unlink;

use const FILEINFO_MIME;
use const LOCK_EX;
use const LOCK_SH;
use const LOCK_UN;

/**
 * Provides file utilities.
 */
class File
{
    use DebugTrait;
    use MacroTrait;

    public const LOCK_EXCLUSIVE = LOCK_EX;

    public const LOCK_SHARED = LOCK_SH;

    public const UNLOCK = LOCK_UN;

    protected readonly Folder $folder;

    /**
     * @var resource|null
     */
    protected mixed $handle = null;

    protected readonly string $path;

    /**
     * Constructs a File.
     *
     * @param string $path The file path.
     * @param bool $create Whether to create the file (if it doesn't exist).
     */
    public function __construct(string $path, bool $create = false)
    {
        $this->path = Path::resolve($path);
        $this->folder = new Folder($this->dirName());

        if ($create && !$this->exists()) {
            $this->create();
        }
    }

    /**
     * Returns the file access time.
     *
     * @return int The file access time.
     *
     * @throws ErrorException|RuntimeException If the access time could not be retrieved.
     */
    public function accessTime(): int
    {
        if (($accessTime = @fileatime($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $accessTime;
    }

    /**
     * Returns the filename.
     *
     * @return string The base name.
     */
    public function baseName(): string
    {
        return Path::baseName($this->path);
    }

    /**
     * Changes the file permissions.
     *
     * @param int $permissions The file permissions.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the permissions could not be updated.
     */
    public function chmod(int $permissions): static
    {
        if (!@chmod($this->path, $permissions)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Closes the file handle.
     *
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the handle could not be closed.
     */
    public function close(): static
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (!fclose($this->handle)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        $this->handle = null;

        return $this;
    }

    /**
     * Returns the contents of the file.
     *
     * @return string The contents of the file.
     *
     * @throws ErrorException|RuntimeException If the contents could not be read.
     */
    public function contents(): string
    {
        if (($contents = @file_get_contents($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $contents;
    }

    /**
     * Copies the file to a new destination.
     *
     * @param string $destination The destination.
     * @param bool $overwrite Whether to overwrite existing files.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the file could not be copied.
     */
    public function copy(string $destination, bool $overwrite = true): static
    {
        if (!$overwrite && file_exists($destination)) {
            throw new RuntimeException(sprintf(
                'File `%s` already exists.',
                $destination
            ));
        }

        if (!@copy($this->path, $destination)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        $permissions = fileperms($this->path) ?: 0777;
        chmod($destination, $permissions);

        $modifiedTime = $this->modifiedTime();
        $accessTime = $this->accessTime();
        touch($destination, $modifiedTime, $accessTime);

        return $this;
    }

    /**
     * Creates the file.
     *
     * @return static The File instance.
     *
     * @throws RuntimeException If the file already exists.
     * @throws ErrorException|RuntimeException If the file could not be created.
     */
    public function create(): static
    {
        if (file_exists($this->path)) {
            throw new RuntimeException(sprintf(
                'File `%s` already exists.',
                $this->path
            ));
        }

        if (!$this->folder()->exists()) {
            $this->folder()->create();
        }

        $this->touch();

        return $this;
    }

    /**
     * Parses CSV values from a file.
     *
     * @param int $length The maximum length to parse.
     * @param string $separator The field separator.
     * @param string $enclosure The field enclosure character.
     * @param string $escape The escape character.
     * @return (string|null)[] The parsed CSV values.
     *
     * @throws ErrorException|RuntimeException If the file could not be parsed.
     */
    public function csv(int $length = 0, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array
    {
        assert($length >= 0);

        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (($data = @fgetcsv($this->handle, $length, $separator, $enclosure, $escape)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $data;
    }

    /**
     * Deletes the file.
     *
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the file could not be deleted.
     */
    public function delete(): static
    {
        if (!@unlink($this->path)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Returns the directory name.
     *
     * @return string The directory name.
     */
    public function dirName(): string
    {
        $dirName = Path::dirName($this->path);

        return $dirName !== '' ? $dirName : DIRECTORY_SEPARATOR;
    }

    /**
     * Checks whether the pointer is at the end of the file.
     *
     * @return bool Whether the pointer is at the end of the file.
     *
     * @throws ErrorException|RuntimeException If the file handle is not valid.
     */
    public function ended(): bool
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        return feof($this->handle);
    }

    /**
     * Checks whether the file exists.
     *
     * @return bool Whether the file exists.
     */
    public function exists(): bool
    {
        return $this->path && is_file($this->path);
    }

    /**
     * Returns the file extension.
     *
     * @return string The file extension.
     */
    public function extension(): string
    {
        return Path::extension($this->path);
    }

    /**
     * Returns the filename (without extension).
     *
     * Note: This may be an empty string for dotfiles (e.g. ".bashrc").
     *
     * @return string The filename (without extension).
     */
    public function fileName(): string
    {
        return Path::fileName($this->path);
    }

    /**
     * Returns the Folder instance.
     *
     * @return Folder The Folder instance.
     */
    public function folder(): Folder
    {
        return $this->folder;
    }

    /**
     * Returns the file group.
     *
     * @return int The file group.
     *
     * @throws ErrorException|RuntimeException If the group could not be retrieved.
     */
    public function group(): int
    {
        if (($group = @filegroup($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $group;
    }

    /**
     * Checks whether the file is executable.
     *
     * @return bool Whether the file is executable.
     */
    public function isExecutable(): bool
    {
        return is_executable($this->path);
    }

    /**
     * Checks whether the file is readable.
     *
     * @return bool Whether the file is readable.
     */
    public function isReadable(): bool
    {
        return is_readable($this->path);
    }

    /**
     * Checks whether the file is writable.
     *
     * @return bool Whether the file is writable.
     */
    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    /**
     * Locks the file handle.
     *
     * @param int|null $operation The lock operation.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the lock could not be acquired.
     */
    public function lock(int|null $operation = null): static
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (!flock($this->handle, $operation ?? static::LOCK_SHARED)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Returns the MIME content type.
     *
     * @return string The MIME content type.
     *
     * @throws ErrorException|RuntimeException If the mime type could not be determined.
     */
    public function mimeType(): string
    {
        if (($type = @new finfo(FILEINFO_MIME)->file($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return strtok($type, ';') ?: 'application/octet-stream';
    }

    /**
     * Returns the file modified time.
     *
     * @return int The file modified time.
     *
     * @throws ErrorException|RuntimeException If the modified time could not be retrieved.
     */
    public function modifiedTime(): int
    {
        if (($modifiedTime = @filemtime($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $modifiedTime;
    }

    /**
     * Opens a file handle.
     *
     * @param string $mode The access mode.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the handle could not be opened.
     */
    public function open(string $mode = 'r'): static
    {
        if (!($this->handle = fopen($this->path, $mode) ?: null)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Returns the file owner.
     *
     * @return int The file owner.
     *
     * @throws ErrorException|RuntimeException If the owner could not be retrieved.
     */
    public function owner(): int
    {
        if (($owner = @fileowner($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $owner;
    }

    /**
     * Returns the full path to the file.
     *
     * @return string The full path.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns the file permissions.
     *
     * @return string The file permissions.
     *
     * @throws ErrorException|RuntimeException If the permissions could not be retrieved.
     */
    public function permissions(): string
    {
        if (($permissions = @fileperms($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return decoct($permissions & 0777);
    }

    /**
     * Reads file data.
     *
     * @param int $length The number of bytes to read.
     * @return string The data.
     *
     * @throws ErrorException|RuntimeException If the data could not be read.
     */
    public function read(int $length): string
    {
        assert($length > 0);

        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (($data = @fread($this->handle, $length)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $data;
    }

    /**
     * Rewinds the pointer position.
     *
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the handle could not be rewound.
     */
    public function rewind(): static
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (!rewind($this->handle)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Moves the pointer position.
     *
     * @param int $offset The new pointer position.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the seek fails.
     */
    public function seek(int $offset): static
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (fseek($this->handle, $offset) !== 0) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Returns the size of the file (in bytes).
     *
     * @return int The size of the file (in bytes).
     *
     * @throws ErrorException|RuntimeException If the size could not be read.
     */
    public function size(): int
    {
        if (($size = @filesize($this->path)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $size;
    }

    /**
     * Returns the current pointer position.
     *
     * @return int The current pointer position.
     *
     * @throws ErrorException|RuntimeException If the offset could not be read.
     */
    public function tell(): int
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (($offset = ftell($this->handle)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $offset;
    }

    /**
     * Touches the file.
     *
     * @param int|null $time The touch time.
     * @param int|null $accessTime The access time.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the file could not be touched.
     */
    public function touch(int|null $time = null, int|null $accessTime = null): static
    {
        $time ??= time();

        if (!touch($this->path, $time, $accessTime ?? $time)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Truncates the file.
     *
     * @param int $size The size to truncate to.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the file could not be truncated.
     */
    public function truncate(int $size = 0): static
    {
        assert($size >= 0);

        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (!ftruncate($this->handle, $size)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Unlocks the file handle.
     *
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the lock could not be released.
     */
    public function unlock(): static
    {
        return $this->lock(static::UNLOCK);
    }

    /**
     * Writes data to the file.
     *
     * @param string $data The data to write.
     * @return static The File instance.
     *
     * @throws ErrorException|RuntimeException If the data could not be written.
     */
    public function write(string $data): static
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('File handle is not valid.');
        }

        if (@fwrite($this->handle, $data) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }
}
