<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Session\Handlers;

use Fyre\Http\Session\SessionHandler;
use Override;
use SessionHandlerInterface;

/**
 * MockSessionHandler
 */
class MockSessionHandler extends SessionHandler implements SessionHandlerInterface
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
    public function write(string $sessionId, string $data): bool
    {
        return true;
    }
}
