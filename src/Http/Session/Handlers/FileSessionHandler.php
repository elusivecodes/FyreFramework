<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use DirectoryIterator;
use Fyre\Core\Attributes\SensitiveProperty;
use Fyre\Http\Session\SessionHandler;
use Fyre\Utility\Path;
use Override;

use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function time;
use function unlink;

use const LOCK_EX;

/**
 * Stores each session as a file under the configured save path. Files are named using the
 * handler prefix + session id.
 */
class FileSessionHandler extends SessionHandler
{
    #[SensitiveProperty]
    protected string $path;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function destroy(string $sessionId): bool
    {
        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

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
        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        return (string) @file_get_contents($filePath);
    }

    /**
     * {@inheritDoc}
     *
     * Writes are locked with `LOCK_EX` to reduce race conditions.
     */
    #[Override]
    public function write(string $sessionId, string $data): bool
    {
        if (!$sessionId) {
            return false;
        }

        $key = $this->prepareKey($sessionId);
        $filePath = Path::join($this->path, $key);

        return file_put_contents($filePath, $data, LOCK_EX) !== false;
    }
}
