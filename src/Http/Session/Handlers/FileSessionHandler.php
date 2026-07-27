<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use DirectoryIterator;
use Fyre\Core\Attributes\SensitiveProperty;
use Fyre\Http\Session\SessionHandler;
use Fyre\Utility\Path;
use Override;

use function fclose;
use function filemtime;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function is_dir;
use function is_resource;
use function mkdir;
use function rewind;
use function stream_get_contents;
use function time;
use function touch;
use function unlink;

use const LOCK_EX;

/**
 * Stores each session as a file under the configured save path. Files are named using the
 * handler prefix + session id.
 */
class FileSessionHandler extends SessionHandler
{
    /**
     * @var resource|null
     */
    protected mixed $handle = null;

    protected string|null $lockedPath = null;

    #[SensitiveProperty]
    protected string $path;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function close(): bool
    {
        if (!is_resource($this->handle)) {
            $this->handle = null;
            $this->lockedPath = null;

            return true;
        }

        $handle = $this->handle;

        $this->handle = null;
        $this->lockedPath = null;

        return @fclose($handle);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function destroy(string $sessionId): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        if ($this->lockedPath === $filePath && !$this->close()) {
            return false;
        }

        @unlink($filePath);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function gc(int $expires): false|int
    {
        $maxLife = time() - $expires;

        $iterator = new DirectoryIterator($this->path);

        $deleted = 0;
        foreach ($iterator as $item) {
            if (
                $item->isDir() ||
                $item->getMTime() >= $maxLife
            ) {
                continue;
            }

            $filePath = $item->getPathname();
            @unlink($filePath);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * {@inheritDoc}
     *
     * Ensures the session directory exists.
     */
    #[Override]
    public function open(string $path, string $name): bool
    {
        if (!$this->close()) {
            return false;
        }

        $this->path = $path;

        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Note: Missing files return an empty string. Errors are suppressed.
     */
    #[Override]
    public function read(string $sessionId): false|string
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        $handle = $this->lockFile($filePath);

        if (
            !is_resource($handle) ||
            !@rewind($handle)
        ) {
            $this->close();

            return false;
        }

        $data = @stream_get_contents($handle);

        if ($data === false) {
            $this->close();
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        if (!$this->validateId($sessionId)) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        return touch($filePath);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function validateId(string $sessionId): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);
        $modified = @filemtime($filePath);

        return $modified !== false && $modified >= time() - $this->config['expires'];
    }

    /**
     * {@inheritDoc}
     *
     * The lock acquired during read is retained until the session is closed.
     */
    #[Override]
    public function write(string $sessionId, string $data): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        $handle = $this->lockFile($filePath);

        if (
            !is_resource($handle) ||
            !@rewind($handle) ||
            !@ftruncate($handle, 0)
        ) {
            return false;
        }

        return @fwrite($handle, $data) !== false;
    }

    /**
     * Locks a session file.
     *
     * @param string $filePath The file path.
     * @return false|resource The file handle, or false if the file could not be locked.
     */
    protected function lockFile(string $filePath): mixed
    {
        if (
            $this->lockedPath === $filePath &&
            is_resource($this->handle)
        ) {
            return $this->handle;
        }

        if (!$this->close()) {
            return false;
        }

        $handle = @fopen($filePath, 'c+b');

        if (!is_resource($handle)) {
            return false;
        }

        if (!@flock($handle, LOCK_EX)) {
            @fclose($handle);

            return false;
        }

        $this->handle = $handle;
        $this->lockedPath = $filePath;

        return $handle;
    }
}
