<?php
declare(strict_types=1);

namespace Fyre\Core;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Arr;
use Fyre\Utility\Path;

use function array_replace_recursive;
use function array_splice;
use function array_unshift;
use function file_exists;
use function in_array;
use function is_array;

/**
 * Provides configuration storage and lookup using "dot" notation.
 */
class Config
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * @var string[]
     */
    protected array $paths = [];

    /**
     * Adds a config path.
     *
     * The path is first resolved/normalized via Path::resolve(), so equivalent
     * paths will not be duplicated.
     *
     * Paths are processed in the order returned by {@see Config::getPaths()} when calling
     * {@see Config::load()}. Later paths take precedence over earlier paths when merging config.
     *
     * @param string $path The path to add.
     * @param bool $prepend Whether to prepend the path.
     * @return static The Config instance.
     */
    public function addPath(string $path, bool $prepend = false): static
    {
        $path = Path::resolve($path);

        if (!in_array($path, $this->paths, true)) {
            if ($prepend) {
                array_unshift($this->paths, $path);
            } else {
                $this->paths[] = $path;
            }
        }

        return $this;
    }

    /**
     * Clears config data.
     */
    public function clear(): void
    {
        $this->paths = [];
        $this->config = [];
    }

    /**
     * Retrieves and deletes a value from the config using "dot" notation.
     *
     * Returns the value (or default) and then removes it from the config.
     *
     * @param string $key The config key.
     * @param mixed $default The default value.
     * @return mixed The value.
     */
    public function consume(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->delete($key);

        return $value;
    }

    /**
     * Deletes a value from the config using "dot" notation.
     *
     * @param string $key The config key.
     * @return static The Config instance.
     */
    public function delete(string $key): static
    {
        $this->config = Arr::forgetDot($this->config, $key);

        return $this;
    }

    /**
     * Retrieves a value from the config using "dot" notation.
     *
     * @param string $key The config key.
     * @param mixed $default The default value.
     * @return mixed The config value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::getDot($this->config, $key, $default);
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
     * Checks whether a value exists in the config using "dot" notation.
     *
     * @param string $key The config key.
     * @return bool Whether the config key exists.
     */
    public function has(string $key): bool
    {
        return Arr::hasDot($this->config, $key);
    }

    /**
     * Loads a file into the config.
     *
     * The file name should be given without extension; ".php" will be appended.
     * All config arrays found across configured paths are merged recursively in
     * the order the paths were added (later paths override earlier paths).
     * Missing files and non-array results are ignored.
     *
     * @param string $file The base file name to load (without ".php").
     * @return static The Config instance.
     */
    public function load(string $file): static
    {
        $file .= '.php';

        foreach ($this->paths as $path) {
            $filePath = Path::join($path, $file);

            if (!file_exists($filePath)) {
                continue;
            }

            $config = require $filePath;

            if (!is_array($config)) {
                continue;
            }

            $this->config = array_replace_recursive($this->config, $config);
        }

        return $this;
    }

    /**
     * Removes a path.
     *
     * @param string $path The path to remove.
     * @return static The Config instance.
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
     * Sets a config value using "dot" notation.
     *
     * Supports wildcard segments (`*`) via {@see Arr::setDot()}.
     *
     * @param string $key The config key.
     * @param mixed $value The config value.
     * @param bool $overwrite Whether to overwrite previous values.
     * @return static The Config instance.
     */
    public function set(string $key, mixed $value, bool $overwrite = true): static
    {
        $this->config = Arr::setDot($this->config, $key, $value, $overwrite);

        return $this;
    }
}
