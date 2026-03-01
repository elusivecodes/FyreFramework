<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Override;

/**
 * No-op cache handler.
 */
class NullCacher extends Cacher
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function clear(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $key): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function increment(string $key, int $amount = 1): int
    {
        return $amount;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function set(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        return true;
    }
}
