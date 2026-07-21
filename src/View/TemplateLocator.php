<?php
declare(strict_types=1);

namespace Fyre\View;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Path;

use function array_splice;
use function explode;
use function in_array;
use function is_file;
use function preg_replace;
use function realpath;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strtolower;

use const DIRECTORY_SEPARATOR;

/**
 * Locates template files by name and configured paths.
 *
 * Note: Paths are searched in the order they were added. The `.php` extension is appended
 * automatically when missing.
 */
class TemplateLocator
{
    use DebugTrait;

    public const CELLS_FOLDER = 'cells';

    public const ELEMENTS_FOLDER = 'elements';

    public const LAYOUTS_FOLDER = 'layouts';

    protected const FILE_EXTENSION = '.php';

    /**
     * @var string[]
     */
    protected array $paths = [];

    /**
     * Normalizes a file name.
     *
     * Note: This converts camelCase/PascalCase to snake_case.
     *
     * @param string $string The input string.
     * @return string The normalized string.
     */
    public static function normalize(string $string): string
    {
        return ((string) preg_replace('/(?<=[^A-Z])[A-Z]/', '_\0', $string)) |> strtolower(...);
    }

    /**
     * Adds a path for loading templates.
     *
     * @param string $path The path.
     * @return static The TemplateLocator.
     */
    public function addPath(string $path): static
    {
        $path = Path::resolve($path);

        if (!in_array($path, $this->paths, true)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Clears all paths.
     */
    public function clear(): void
    {
        $this->paths = [];
    }

    /**
     * Returns the paths.
     *
     * @return string[] The paths.
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Finds a file in paths.
     *
     * Note: The `$folder` is appended between the base path and file name.
     * Paths are searched in the order they were added.
     *
     * @param string $name The file name.
     * @param string $folder The file folder.
     * @return string|null The file path.
     */
    public function locate(string $name, string $folder = ''): string|null
    {
        if (!static::isSafeRelativePath($name) || !static::isSafeRelativePath($folder)) {
            return null;
        }

        if (!str_ends_with($name, static::FILE_EXTENSION)) {
            $name .= static::FILE_EXTENSION;
        }

        foreach ($this->paths as $path) {
            $rootPath = realpath($path);

            if ($rootPath === false) {
                continue;
            }

            $filePath = Path::join($rootPath, $folder, $name) |> realpath(...);

            if ($filePath === false || !is_file($filePath)) {
                continue;
            }

            $rootPrefix = rtrim($rootPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            if (!str_starts_with($filePath, $rootPrefix)) {
                continue;
            }

            return $filePath;
        }

        return null;
    }

    /**
     * Removes a path.
     *
     * @param string $path The path to remove.
     * @return static The TemplateLocator.
     */
    public function removePath(string $path): static
    {
        $path = Path::resolve($path);

        foreach ($this->paths as $i => $otherPath) {
            if ($otherPath !== $path) {
                continue;
            }

            array_splice($this->paths, $i, 1);
            break;
        }

        return $this;
    }

    /**
     * Checks whether a template path is safe to resolve relative to a configured root.
     *
     * This is a lexical check performed before accessing the filesystem. It rejects null
     * bytes, absolute paths, and complete parent-directory (`..`) segments using either
     * slash style. The candidate is later resolved with `realpath()` and checked against
     * its canonical root, which also prevents traversal through symbolic links.
     *
     * @param string $path The path.
     * @return bool Whether the path can be safely joined to a configured root.
     */
    protected static function isSafeRelativePath(string $path): bool
    {
        if (str_contains($path, "\0") || Path::isAbsolute($path)) {
            return false;
        }

        $normalized = str_replace('\\', '/', $path);
        $segments = explode('/', $normalized);

        return !in_array('..', $segments, true);
    }
}
