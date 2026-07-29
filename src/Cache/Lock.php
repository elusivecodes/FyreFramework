<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Fyre\Cache\Exceptions\InvalidArgumentException;

use function bin2hex;
use function hrtime;
use function min;
use function random_bytes;
use function usleep;

/**
 * Represents an owner-specific cache lock.
 */
abstract class Lock
{
    protected const RETRY_DELAY = 10000;

    protected bool $acquired = false;

    protected string $owner;

    /**
     * Constructs a Lock.
     *
     * @param string $key The lock key.
     * @param int $expires The lock lifetime in seconds.
     *
     * @throws InvalidArgumentException If the expiration is not valid.
     */
    public function __construct(
        protected string $key,
        protected int $expires = 30
    ) {
        if ($this->expires < 1) {
            throw new InvalidArgumentException('Cache lock expiration must be greater than 0.');
        }

        $this->owner = random_bytes(16) |> bin2hex(...);
    }

    /**
     * Acquires the lock.
     *
     * @param float $wait The maximum number of seconds to wait.
     * @return bool Whether the lock was acquired.
     *
     * @throws InvalidArgumentException If the wait time is not valid.
     */
    public function acquire(float $wait = 0): bool
    {
        if ($wait < 0) {
            throw new InvalidArgumentException('Cache lock wait time must not be negative.');
        }

        if ($this->acquired) {
            return $this->refresh();
        }

        $deadline = hrtime(true) + (int) ($wait * 1_000_000_000);

        do {
            if ($this->acquireLock()) {
                $this->acquired = true;

                return true;
            }

            $remaining = $deadline - hrtime(true);

            if ($remaining <= 0) {
                return false;
            }

            usleep((int) min(static::RETRY_DELAY, $remaining / 1000));
        } while (true);
    }

    /**
     * Checks whether the lock has been acquired by this object.
     *
     * @return bool Whether the lock has been acquired.
     */
    public function isAcquired(): bool
    {
        return $this->acquired;
    }

    /**
     * Refreshes the lock lifetime.
     *
     * @return bool Whether the lock was refreshed.
     */
    public function refresh(): bool
    {
        if (!$this->acquired) {
            return false;
        }

        if (!$this->refreshLock()) {
            $this->acquired = false;

            return false;
        }

        return true;
    }

    /**
     * Releases the lock.
     *
     * @return bool Whether the lock was released.
     */
    public function release(): bool
    {
        if (!$this->acquired) {
            return false;
        }

        try {
            return $this->releaseLock();
        } finally {
            $this->acquired = false;
        }
    }

    /**
     * Attempts to acquire the backend lock.
     *
     * @return bool Whether the lock was acquired.
     */
    abstract protected function acquireLock(): bool;

    /**
     * Attempts to refresh the backend lock.
     *
     * @return bool Whether the lock was refreshed.
     */
    abstract protected function refreshLock(): bool;

    /**
     * Attempts to release the backend lock.
     *
     * @return bool Whether the lock was released.
     */
    abstract protected function releaseLock(): bool;
}
