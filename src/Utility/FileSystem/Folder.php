<?php
declare(strict_types=1);

namespace Fyre\Utility\FileSystem;

use FileSystemIterator;
use Fyre\Core\Exceptions\ErrorException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

use function assert;
use function chmod;
use function copy;
use function file_exists;
use function fileatime;
use function filemtime;
use function fileperms;
use function is_dir;
use function mkdir;
use function rmdir;
use function sprintf;
use function str_replace;
use function touch;
use function unlink;

/**
 * Provides folder utilities.
 */
class Folder
{
    use DebugTrait;
    use MacroTrait;

    protected readonly string $path;

    /**
     * Constructs a Folder.
     *
     * @param string $path The folder path.
     * @param bool $create Whether to create the folder (if it doesn't exist).
     * @param int $permissions The permissions.
     */
    public function __construct(string $path, bool $create = false, int $permissions = 0755)
    {
        $this->path = Path::resolve($path);

        if ($create && !$this->exists()) {
            $this->create($permissions);
        }
    }

    /**
     * Returns the contents of the folder.
     *
     * @return (File|Folder)[] The contents of the folder.
     */
    public function contents(): array
    {
        $iterator = new FileSystemIterator($this->path);

        $contents = [];

        foreach ($iterator as $item) {
            assert($item instanceof SplFileInfo);

            $filePath = $item->getPathname();

            if ($item->isDir()) {
                $contents[] = new static($filePath);
            } else {
                $contents[] = new File($filePath);
            }
        }

        return $contents;
    }

    /**
     * Copies the folder to a new destination.
     *
     * @param string $destination The destination.
     * @param bool $overwrite Whether to overwrite existing files.
     * @return static The Folder.
     *
     * @throws ErrorException|RuntimeException If the folder could not be copied.
     */
    public function copy(string $destination, bool $overwrite = true): static
    {
        $iterator = $this->getIterator(RecursiveIteratorIterator::SELF_FIRST);
        $destination = Path::resolve($destination);

        $permissions = fileperms($this->path) ?: 0777;

        if (!file_exists($destination) && !is_dir($destination) && !@mkdir($destination, $permissions, true)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        foreach ($iterator as $item) {
            $filePath = $item->getPathname();
            $newPath = str_replace($this->path, $destination, $filePath);

            $permissions = fileperms($filePath) ?: 0777;

            if ($item->isDir()) {
                if (!is_dir($newPath) && !@mkdir($newPath, $permissions, true)) {
                    throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
                }
            } else {
                if (!$overwrite && file_exists($newPath)) {
                    throw new RuntimeException(sprintf(
                        'File `%s` already exists.',
                        $newPath
                    ));
                }

                if (!@copy($filePath, $newPath)) {
                    throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
                }

                chmod($newPath, $permissions);

                $modifiedTime = filemtime($filePath) ?: null;
                $accessTime = fileatime($filePath) ?: null;
                touch($newPath, $modifiedTime, $accessTime);
            }
        }

        return $this;
    }

    /**
     * Creates the folder.
     *
     * @param int $permissions The permissions.
     * @return static The Folder.
     *
     * @throws ErrorException|RuntimeException If the folder exists or creation fails.
     */
    public function create(int $permissions = 0755): static
    {
        if (!@mkdir($this->path, $permissions, true)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Deletes the folder.
     *
     * @return static The Folder.
     *
     * @throws ErrorException|RuntimeException If the folder could not be removed.
     */
    public function delete(): static
    {
        $this->empty();

        if (!@rmdir($this->path)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $this;
    }

    /**
     * Empties the folder.
     *
     * @return static The Folder.
     *
     * @throws ErrorException|RuntimeException If the folder could not be emptied.
     */
    public function empty(): static
    {
        foreach ($this->getIterator(RecursiveIteratorIterator::CHILD_FIRST) as $item) {
            $filePath = $item->getPathname();

            if ($item->isDir()) {
                if (!@rmdir($filePath)) {
                    throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
                }
            } else {
                if (!@unlink($filePath)) {
                    throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
                }
            }
        }

        return $this;
    }

    /**
     * Checks whether the folder exists.
     *
     * @return bool Whether the folder exists.
     */
    public function exists(): bool
    {
        return $this->path && is_dir($this->path);
    }

    /**
     * Checks whether the folder is empty.
     *
     * @return bool Whether the folder is empty.
     */
    public function isEmpty(): bool
    {
        return !new FileSystemIterator($this->path)->valid();
    }

    /**
     * Moves the folder to a new destination.
     *
     * @param string $destination The destination.
     * @param bool $overwrite Whether to overwrite existing files.
     * @return static The Folder.
     */
    public function move(string $destination, bool $overwrite = true): static
    {
        $this->copy($destination, $overwrite);
        $this->delete();

        return new static($destination);
    }

    /**
     * Returns the folder name.
     *
     * @return string The folder name.
     */
    public function name(): string
    {
        $name = Path::baseName($this->path);

        return $name !== '' ? $name : DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the full path to the folder.
     *
     * @return string The full path.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns the size of the folder (in bytes).
     *
     * @return int The size of the folder (in bytes).
     */
    public function size(): int
    {
        $size = 0;
        foreach ($this->getIterator(RecursiveIteratorIterator::SELF_FIRST) as $item) {
            $size += $item->getSize();
        }

        return $size;
    }

    /**
     * Gets a recursive iterator for the folder.
     *
     * @param int $mode The iterator mode.
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator> The recursive iterator.
     */
    protected function getIterator(int $mode): RecursiveIteratorIterator
    {
        assert($mode >= 0);
        assert($mode <= 2);

        $directory = new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS);

        return new RecursiveIteratorIterator($directory, $mode);
    }
}
