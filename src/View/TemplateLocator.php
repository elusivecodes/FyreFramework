<?php
declare(strict_types=1);

namespace Fyre\View;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Path;

use function array_splice;
use function in_array;
use function is_file;
use function preg_replace;
use function str_ends_with;
use function strtolower;

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
        if (!str_ends_with($name, static::FILE_EXTENSION)) {
            $name .= static::FILE_EXTENSION;
        }

        foreach ($this->paths as $path) {
            $filePath = Path::join($path, $folder, $name);

            if (is_file($filePath)) {
                return $filePath;
            }
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
}
