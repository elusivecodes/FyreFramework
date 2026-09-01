<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\File;

use Fyre\Cache\Lock;
use Override;

use function chmod;
use function clearstatcache;
use function fclose;
use function fflush;
use function flock;
use function fopen;
use function fstat;
use function ftruncate;
use function fwrite;
use function is_resource;
use function rewind;
use function serialize;
use function stat;
use function stream_get_contents;
use function substr;
use function time;
use function unlink;

use const LOCK_EX;

/**
 * Provides owner-token locking using a file.
 */
class FileLock extends Lock
{
    /**
     * Constructs a FileLock.
     *
     * @param string $filePath The lock file path.
     * @param int $expires The lock lifetime in seconds.
     * @param int $mode The lock file permissions.
     */
    public function __construct(
        string $filePath,
        int $expires = 30,
        protected int $mode = 0640
    ) {
        parent::__construct($filePath, $expires);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function acquireLock(): bool
    {
        $handle = $this->openLock();

        if ($handle === false) {
            return false;
        }

        try {
            $lock = $this->readLock($handle);

            if ($lock === false) {
                return false;
            }

            $now = time();

            if ($lock && !$lock->isExpired($now)) {
                return false;
            }

            return $this->writeLock(
                $handle,
                new LockEntry($now + $this->expires, $this->owner)
            );
        } finally {
            @fclose($handle);
        }
    }

    /**
     * Checks whether a handle points to the current lock file.
     *
     * @param resource $handle The file handle.
     * @return bool Whether the handle points to the current lock file.
     */
    protected function isCurrentFile(mixed $handle): bool
    {
        clearstatcache(true, $this->key);

        $fileStat = @stat($this->key);
        $handleStat = @fstat($handle);

        return $fileStat !== false &&
            $handleStat !== false &&
            $fileStat['dev'] === $handleStat['dev'] &&
            $fileStat['ino'] === $handleStat['ino'];
    }

    /**
     * Opens and locks the lock file.
     *
     * @return false|resource The file handle, or false if the file could not be locked.
     */
    protected function openLock(): mixed
    {
        $handle = @fopen($this->key, 'c+b');

        if (!is_resource($handle)) {
            return false;
        }

        if (!@flock($handle, LOCK_EX) || !$this->isCurrentFile($handle)) {
            @fclose($handle);

            return false;
        }

        @chmod($this->key, $this->mode);

        return $handle;
    }

    /**
     * Reads the lock data.
     *
     * @param resource $handle The file handle.
     * @return false|LockEntry|null The lock data, false on failure, or null if no valid lock exists.
     */
    protected function readLock(mixed $handle): false|LockEntry|null
    {
        if (!@rewind($handle)) {
            return false;
        }

        $contents = @stream_get_contents($handle);

        if ($contents === false) {
            return false;
        }

        return LockEntry::createFromString($contents);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function refreshLock(): bool
    {
        $handle = $this->openLock();

        if ($handle === false) {
            return false;
        }

        try {
            $lock = $this->readLock($handle);

            if (!$lock || !$lock->isOwnedBy($this->owner)) {
                return false;
            }

            $now = time();

            if ($lock->isExpired($now)) {
                return false;
            }

            return $this->writeLock(
                $handle,
                new LockEntry($now + $this->expires, $this->owner)
            );
        } finally {
            @fclose($handle);
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function releaseLock(): bool
    {
        $handle = $this->openLock();

        if ($handle === false) {
            return false;
        }

        try {
            $lock = $this->readLock($handle);

            if (
                !$lock ||
                !$lock->isOwnedBy($this->owner) ||
                $lock->isExpired()
            ) {
                return false;
            }

            return @unlink($this->key) || $this->writeLock($handle, null);
        } finally {
            @fclose($handle);
        }
    }

    /**
     * Writes the lock data.
     *
     * @param resource $handle The file handle.
     * @param LockEntry|null $lock The lock data.
     * @return bool Whether the lock data was written.
     */
    protected function writeLock(mixed $handle, LockEntry|null $lock): bool
    {
        $data = $lock === null ?
            '' :
            serialize($lock);

        if (!@rewind($handle) || !@ftruncate($handle, 0)) {
            return false;
        }

        while ($data !== '') {
            $written = @fwrite($handle, $data);

            if ($written === false || $written === 0) {
                return false;
            }

            $data = substr($data, $written);
        }

        return @fflush($handle);
    }
}
