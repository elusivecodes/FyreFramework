<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Closure;
use DateInterval;
use DateTimeImmutable;
use Fyre\Cache\Exceptions\InvalidArgumentException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Override;
use Psr\SimpleCache\CacheInterface;
use stdClass;

use function array_replace;
use function is_int;
use function sprintf;
use function strpbrk;

/**
 * Provides a base Cacher implementation.
 */
abstract class Cacher implements CacheInterface
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'expire' => null,
        'prefix' => '',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs a Cacher.
     *
     * @param array<string, mixed> $options The Cacher options.
     */
    public function __construct(array $options = [])
    {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
    }

    /**
     * Decrements a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to decrement.
     * @return false|int The new value.
     */
    public function decrement(string $key, int $amount = 1): false|int
    {
        return $this->increment($key, -$amount);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Returns the config.
     *
     * @return array<string, mixed> The config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function has(string $key): bool
    {
        $test = new stdClass();

        return $this->get($key, $test) !== $test;
    }

    /**
     * Increments a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return false|int The new value.
     */
    abstract public function increment(string $key, int $amount = 1): false|int;

    /**
     * Retrieves an item from the cache, or saves a new value if it does not exist.
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
     * {@inheritDoc}
     *
     * @param iterable<string, mixed> $values The values to set.
     */
    #[Override]
    public function setMultiple(iterable $values, DateInterval|int|null $expire = null): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $expire)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the expiration interval in seconds.
     *
     * @param DateInterval|int|null $expire The number of seconds the value will be valid, or a DateInterval TTL.
     * @return int|null The expiration interval in seconds or null when no expiration is set.
     */
    protected function getExpires(DateInterval|int|null $expire): int|null
    {
        if ($expire === null) {
            return $this->config['expire'];
        }

        if (is_int($expire)) {
            return $expire;
        }

        $start = new DateTimeImmutable();
        $end = $start->add($expire);

        return $end->getTimestamp() - $start->getTimestamp();
    }

    /**
     * Returns the real cache key.
     *
     * @param string $key The cache key.
     * @return string The real cache key with the configured prefix applied.
     *
     * @throws InvalidArgumentException If the cache key is not valid.
     */
    protected function prepareKey(string $key): string
    {
        if (strpbrk($key, '{}()/\@:') !== false) {
            throw new InvalidArgumentException(sprintf(
                'Cache key `%s` is not valid.',
                $key
            ));
        }

        return $this->config['prefix'].$key;
    }
}
