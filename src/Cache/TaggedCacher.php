<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Closure;
use DateInterval;
use Fyre\Core\Traits\DebugTrait;
use stdClass;

use function array_key_exists;
use function array_unique;
use function array_values;
use function bin2hex;
use function implode;
use function is_array;
use function random_bytes;
use function sha1;
use function sort;

/**
 * Provides a tagged cache wrapper.
 *
 * @phpstan-consistent-constructor
 */
class TaggedCacher
{
    use DebugTrait;

    /**
     * Generates a tag invalidation token.
     *
     * @return string The tag invalidation token.
     */
    public static function generateToken(): string
    {
        return random_bytes(16) |> bin2hex(...);
    }

    /**
     * Returns the internal tag metadata key.
     *
     * @param string $tag The tag.
     * @return string The internal tag metadata key.
     */
    public static function tagKey(string $tag): string
    {
        return '__tag__.'.$tag;
    }

    /**
     * Constructs a TaggedCacher.
     *
     * @param string[] $tags The tags.
     */
    public function __construct(
        protected Cacher $cacher,
        protected array $tags
    ) {
        $this->tags = array_unique($this->tags) |> array_values(...);

        sort($this->tags);
    }

    /**
     * Deletes a tagged cache value.
     *
     * @param string $key The cache key.
     * @return bool Whether the key was deleted.
     */
    public function delete(string $key): bool
    {
        return $this->taggedKey($key) |> $this->cacher->delete(...);
    }

    /**
     * Retrieves a tagged cache value.
     *
     * @param string $key The cache key.
     * @param mixed $default The default value.
     * @return mixed The cached value or default value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $test = new stdClass();

        $payload = $this->cacher->get($this->taggedKey($key), $test);

        if ($payload === $test) {
            return $default;
        }

        if (
            !is_array($payload) ||
            !isset($payload['tags']) ||
            !is_array($payload['tags']) ||
            !array_key_exists('value', $payload)
        ) {
            return $default;
        }

        if ($payload['tags'] !== $this->currentTagVersions()) {
            $this->delete($key);

            return $default;
        }

        return $payload['value'];
    }

    /**
     * Retrieves a tagged cache item, or saves a new value if it does not exist.
     *
     * @param string $key The cache key.
     * @param Closure $callback The callback to generate the value.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid, or a DateInterval TTL.
     * @return mixed The cached value.
     */
    public function remember(string $key, Closure $callback, DateInterval|int|null $expire = null): mixed
    {
        $test = new stdClass();

        $value = $this->get($key, $test);

        if ($value !== $test) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $expire);

        return $value;
    }

    /**
     * Sets a tagged cache value.
     *
     * @param string $key The cache key.
     * @param mixed $value The cache value.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid, or a DateInterval TTL.
     * @return bool Whether the value was cached.
     */
    public function set(string $key, mixed $value, DateInterval|int|null $expire = null): bool
    {
        return $this->cacher->set(
            $this->taggedKey($key),
            [
                'tags' => $this->currentTagVersions(),
                'value' => $value,
            ],
            $expire
        );
    }

    /**
     * Returns a new tagged cache wrapper with merged tags.
     *
     * @param string|string[] $tags The tags.
     * @return static The tagged cache wrapper.
     */
    public function tags(array|string $tags): static
    {
        return new static(
            $this->cacher,
            [
                ...$this->tags,
                ...(array) $tags,
            ]
        );
    }

    /**
     * Returns the current tag version snapshot.
     *
     * @return array<string, string>
     */
    protected function currentTagVersions(): array
    {
        $versions = [];

        foreach ($this->tags as $tag) {
            $versions[$tag] = (string) $this->cacher->get(static::tagKey($tag), '0');
        }

        return $versions;
    }

    /**
     * Returns the internal tagged cache key.
     *
     * @param string $key The cache key.
     * @return string The internal tagged cache key.
     */
    protected function taggedKey(string $key): string
    {
        $namespace = implode('|', $this->tags) |> sha1(...);

        return '__tagged__.'.$namespace.'.'.$key;
    }
}
