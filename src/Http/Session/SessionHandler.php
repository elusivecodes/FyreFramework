<?php
declare(strict_types=1);

namespace Fyre\Http\Session;

use Fyre\Core\Traits\DebugTrait;
use Override;
use SessionHandlerInterface;

use function array_replace_recursive;

/**
 * Provides a base class for custom {@see SessionHandlerInterface} implementations.
 *
 * Supplies default configuration and a prefixed session key helper for {@see Session}.
 */
abstract class SessionHandler implements SessionHandlerInterface
{
    use DebugTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'prefix' => '',
        'expires' => 3600,
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Constructs a SessionHandler.
     *
     * @param Session $session The Session.
     * @param array<string, mixed> $options The options for the handler.
     */
    public function __construct(
        protected Session $session,
        array $options = []
    ) {
        $this->config = array_replace_recursive(self::$defaults, static::$defaults, $options);
    }

    /**
     * Closes the session.
     *
     * @return bool Whether the session was closed.
     */
    #[Override]
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroys the session.
     *
     * @param string $sessionId The session ID.
     * @return bool Whether the session was destroyed.
     */
    #[Override]
    abstract public function destroy(string $sessionId): bool;

    /**
     * Runs the session garbage collector.
     *
     * @param int $expires The maximum session lifetime.
     * @return false|int The number of sessions removed.
     */
    #[Override]
    abstract public function gc(int $expires): false|int;

    /**
     * Opens the session.
     *
     * @param string $path The session path.
     * @param string $name The session name.
     * @return bool Whether the session was opened.
     */
    #[Override]
    abstract public function open(string $path, string $name): bool;

    /**
     * Reads the session data.
     *
     * @param string $sessionId The session ID.
     * @return false|string The session data.
     */
    #[Override]
    abstract public function read(string $sessionId): false|string;

    /**
     * Writes the session data.
     *
     * @param string $sessionId The session ID.
     * @param string $data The session data.
     * @return bool Whether the data was written.
     */
    #[Override]
    abstract public function write(string $sessionId, string $data): bool;

    /**
     * Returns the session key.
     *
     * Prefixes the session id to create the underlying storage key.
     *
     * @param string $sessionId The session ID.
     * @return string The session key.
     */
    protected function prepareKey(string $sessionId): string
    {
        return $this->config['prefix'].$sessionId;
    }
}
