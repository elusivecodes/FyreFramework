<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Null;

use Fyre\Cache\Lock;

/**
 * Provides no-op cache locking.
 */
class NullLock extends Lock
{
    /**
     * {@inheritDoc}
     */
    protected function acquireLock(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function refreshLock(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function releaseLock(): bool
    {
        return true;
    }
}
