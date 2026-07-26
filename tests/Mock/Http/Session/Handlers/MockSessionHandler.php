<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Session\Handlers;

use Fyre\Http\Session\SessionHandler;
use Override;

/**
 * MockSessionHandler
 */
class MockSessionHandler extends SessionHandler
{
    #[Override]
    public function close(): bool
    {
        return true;
    }

    #[Override]
    public function destroy(string $sessionId): bool
    {
        return true;
    }

    #[Override]
    public function gc(int $expires): false|int
    {
        return 1;
    }

    #[Override]
    public function open(string $path, string $name): bool
    {
        return true;
    }

    #[Override]
    public function read(string $sessionId): false|string
    {
        return '';
    }

    #[Override]
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        return true;
    }

    #[Override]
    public function validateId(string $sessionId): bool
    {
        return static::isValidSessionId($sessionId);
    }

    #[Override]
    public function write(string $sessionId, string $data): bool
    {
        return true;
    }
}
