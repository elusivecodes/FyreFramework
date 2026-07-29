<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\File;

use Fyre\Cache\Lock;

use function array_key_exists;
use function chmod;
use function clearstatcache;
use function fclose;
use function fflush;
use function flock;
use function fopen;
use function fstat;
use function ftruncate;
use function fwrite;
use function is_array;
use function is_int;
use function is_resource;
use function is_string;
use function rewind;
use function serialize;
use function stat;
use function stream_get_contents;
use function time;
use function unlink;
use function unserialize;

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

            if ($lock && $lock['expires'] > $now) {
                return false;
            }

            return $this->writeLock($handle, [
                'expires' => $now + $this->expires,
                'owner' => $this->owner,
            ]);
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
     * @return array{expires: int, owner: string}|false|null The lock data, false on failure, or null if no valid lock exists.
     */
    protected function readLock(mixed $handle): array|false|null
    {
        if (!@rewind($handle)) {
            return false;
        }

        $contents = @stream_get_contents($handle);

        if ($contents === false) {
            return false;
        }

        $lock = @unserialize($contents);

        if (
            !is_array($lock) ||
            !array_key_exists('expires', $lock) ||
            !array_key_exists('owner', $lock) ||
            !is_int($lock['expires']) ||
            !is_string($lock['owner'])
        ) {
            return null;
        }

        return $lock;
    }

    /**
     * {@inheritDoc}
     */
    protected function refreshLock(): bool
    {
        $handle = $this->openLock();

        if ($handle === false) {
            return false;
        }

        try {
            $lock = $this->readLock($handle);

            if (!$lock || $lock['owner'] !== $this->owner) {
                return false;
            }

            $now = time();

            if ($lock['expires'] <= $now) {
                return false;
            }

            $lock['expires'] = $now + $this->expires;

            return $this->writeLock($handle, $lock);
        } finally {
            @fclose($handle);
        }
    }

    /**
     * {@inheritDoc}
     */
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
                $lock['owner'] !== $this->owner ||
                $lock['expires'] <= time()
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
     * @param array{expires: int, owner: string}|null $lock The lock data.
     * @return bool Whether the lock data was written.
     */
    protected function writeLock(mixed $handle, array|null $lock): bool
    {
        $data = $lock === null ?
            '' :
            serialize($lock);

        if (
            !@rewind($handle) ||
            !@ftruncate($handle, 0) ||
            ($data !== '' && @fwrite($handle, $data) === false)
        ) {
            return false;
        }

        return @fflush($handle);
    }
}
