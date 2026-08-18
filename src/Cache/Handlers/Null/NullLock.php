<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Null;

use Fyre\Cache\Lock;
use Override;

/**
 * Provides no-op cache locking.
 */
class NullLock extends Lock
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function acquireLock(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function refreshLock(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function releaseLock(): bool
    {
        return true;
    }
}
