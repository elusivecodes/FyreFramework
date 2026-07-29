<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Memcached;

use Fyre\Cache\Lock;
use Memcached;

use function array_key_exists;
use function is_array;
use function time;

/**
 * Provides owner-token locking using Memcached.
 */
class MemcachedLock extends Lock
{
    protected const AVAILABLE = '';

    /**
     * Constructs a MemcachedLock.
     *
     * @param Memcached $connection The Memcached connection.
     * @param string $key The lock key.
     * @param int $expires The lock lifetime in seconds.
     */
    public function __construct(
        protected Memcached $connection,
        string $key,
        int $expires = 30
    ) {
        parent::__construct($key, $expires);
    }

    /**
     * {@inheritDoc}
     */
    protected function acquireLock(): bool
    {
        $expires = $this->getExpiry();

        if ($this->connection->add($this->key, $this->owner, $expires)) {
            return true;
        }

        return $this->swapLock(static::AVAILABLE, $this->owner);
    }

    /**
     * Returns the Memcached expiry value.
     *
     * @return int The expiry value.
     */
    protected function getExpiry(): int
    {
        return $this->expires > 2_592_000 ?
            time() + $this->expires :
            $this->expires;
    }

    /**
     * Returns the current lock value and CAS token.
     *
     * @return array{cas: float|int|string, value: string}|null The lock data.
     */
    protected function getLock(): array|null
    {
        $lock = $this->connection->get($this->key, null, Memcached::GET_EXTENDED);

        if (
            $this->connection->getResultCode() !== Memcached::RES_SUCCESS ||
            !is_array($lock) ||
            !isset($lock['cas']) ||
            !array_key_exists('value', $lock)
        ) {
            return null;
        }

        return [
            'cas' => $lock['cas'],
            'value' => (string) $lock['value'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function refreshLock(): bool
    {
        return $this->swapLock($this->owner, $this->owner);
    }

    /**
     * {@inheritDoc}
     */
    protected function releaseLock(): bool
    {
        return $this->swapLock($this->owner, static::AVAILABLE);
    }

    /**
     * Replaces a lock value using its CAS token.
     *
     * @param string $expected The expected lock value.
     * @param string $value The new lock value.
     * @return bool Whether the lock value was replaced.
     */
    protected function swapLock(string $expected, string $value): bool
    {
        $lock = $this->getLock();

        if (!$lock || $lock['value'] !== $expected) {
            return false;
        }

        return $this->connection->cas(
            $lock['cas'], // @phpstan-ignore argument.type
            $this->key,
            $value,
            $this->getExpiry()
        );
    }
}
