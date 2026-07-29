<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Array;

use ArrayObject;
use Fyre\Cache\Lock;

use function time;

/**
 * Provides process-local locking using an in-memory array.
 */
class ArrayLock extends Lock
{
    /**
     * @var ArrayObject<string, array{expires: int, owner: string}>
     */
    protected ArrayObject $locks;

    /**
     * Constructs an ArrayLock.
     *
     * @param ArrayObject<string, array{expires: int, owner: string}> $locks The shared lock data.
     * @param string $key The lock key.
     * @param int $expires The lock lifetime in seconds.
     */
    public function __construct(ArrayObject $locks, string $key, int $expires = 30)
    {
        $this->locks = $locks;

        parent::__construct($key, $expires);
    }

    /**
     * {@inheritDoc}
     */
    protected function acquireLock(): bool
    {
        $lock = $this->locks[$this->key] ?? null;

        if ($lock && $lock['expires'] > time()) {
            return false;
        }

        $this->locks[$this->key] = [
            'expires' => time() + $this->expires,
            'owner' => $this->owner,
        ];

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function refreshLock(): bool
    {
        $lock = $this->locks[$this->key] ?? null;

        if (
            !$lock ||
            $lock['expires'] <= time() ||
            $lock['owner'] !== $this->owner
        ) {
            return false;
        }

        $this->locks[$this->key]['expires'] = time() + $this->expires;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function releaseLock(): bool
    {
        $lock = $this->locks[$this->key] ?? null;

        if (
            !$lock ||
            $lock['expires'] <= time() ||
            $lock['owner'] !== $this->owner
        ) {
            return false;
        }

        unset($this->locks[$this->key]);

        return true;
    }
}
