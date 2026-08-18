<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Array;

use Fyre\Cache\Lock;
use Override;

use function time;

/**
 * Provides process-local locking using an in-memory array.
 */
class ArrayLock extends Lock
{
    /**
     * Constructs an ArrayLock.
     *
     * @param array<string, array{expires: int, owner: string}> $locks The shared lock data.
     * @param string $key The lock key.
     * @param int $expires The lock lifetime in seconds.
     */
    public function __construct(
        protected array &$locks,
        string $key,
        int $expires = 30
    ) {
        parent::__construct($key, $expires);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function acquireLock(): bool
    {
        if (
            isset($this->locks[$this->key]) &&
            $this->locks[$this->key]['expires'] > time()
        ) {
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
    #[Override]
    protected function refreshLock(): bool
    {
        if (
            !isset($this->locks[$this->key]) ||
            $this->locks[$this->key]['expires'] <= time() ||
            $this->locks[$this->key]['owner'] !== $this->owner
        ) {
            return false;
        }

        $this->locks[$this->key]['expires'] = time() + $this->expires;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function releaseLock(): bool
    {
        if (
            !isset($this->locks[$this->key]) ||
            $this->locks[$this->key]['expires'] <= time() ||
            $this->locks[$this->key]['owner'] !== $this->owner
        ) {
            return false;
        }

        unset($this->locks[$this->key]);

        return true;
    }
}
