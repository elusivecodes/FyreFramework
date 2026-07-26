<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;

use const LOCK_EX;

/**
 * Represents a file produced by a generator.
 */
class GeneratedFile
{
    /**
     * Constructs a GeneratedFile.
     *
     * @param string $path The destination path.
     * @param string $contents The file contents.
     */
    public function __construct(
        protected string $path,
        protected string $contents
    ) {}

    /**
     * Returns the file contents.
     *
     * @return string The file contents.
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * Returns the destination path.
     *
     * @return string The destination path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Checks whether the destination can be written.
     *
     * @param bool $force Whether an existing file may be replaced.
     * @return bool Whether the destination is valid.
     */
    public function isValid(bool $force = false): bool
    {
        return !is_dir($this->path) && ($force || !file_exists($this->path));
    }

    /**
     * Saves the generated file.
     *
     * @return bool Whether the file was saved.
     */
    public function save(): bool
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            return false;
        }

        return file_put_contents($this->path, $this->contents, LOCK_EX) !== false;
    }
}
