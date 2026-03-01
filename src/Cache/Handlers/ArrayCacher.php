<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Override;

use function array_key_exists;
use function is_numeric;
use function time;

/**
 * Caches values in an in-memory array.
 */
class ArrayCacher extends Cacher
{
    /**
     * @var array<string, array{data: mixed, expires: int|null}>
     */
    protected array $cache = [];

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);

        if (!array_key_exists($key, $this->cache)) {
            return false;
        }

        unset($this->cache[$key]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->prepareKey($key);

        if (!array_key_exists($key, $this->cache)) {
            return $default;
        }

        $data = $this->cache[$key];

        if ($data['expires'] !== null && $data['expires'] < time()) {
            unset($this->cache[$key]);

            return $default;
        }

        return $data['data'];
    }

    /**
     * {@inheritDoc}
     *
     * Note: When the key is missing or the stored value is `null`, the value is initialised to `0`.
     */
    #[Override]
    public function increment(string $key, int $amount = 1): false|int
    {
        if ($this->get($key) === null) {
            $this->set($key, 0);
        }

        $key = $this->prepareKey($key);

        if (!is_numeric($this->cache[$key]['data'])) {
            return false;
        }

        $this->cache[$key]['data'] = (int) $this->cache[$key]['data'];
        $this->cache[$key]['data'] += $amount;

        return $this->cache[$key]['data'];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function set(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);
        $expires = $this->getExpires($expire);

        if ($expires !== null) {
            $expires += time();
        }

        $this->cache[$key] = [
            'data' => $data,
            'expires' => $expires,
        ];

        return true;
    }
}
