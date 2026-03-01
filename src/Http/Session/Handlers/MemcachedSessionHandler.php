<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use Fyre\Http\Session\Exceptions\SessionException;
use Fyre\Http\Session\SessionHandler;
use Memcached;
use MemcachedException;
use Override;

use function sprintf;

/**
 * Stores session data in Memcached with an expiration time (`expires`).
 */
class MemcachedSessionHandler extends SessionHandler
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 1,
        'prefix' => 'session:',
    ];

    protected Memcached $connection;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function close(): bool
    {
        $this->connection->quit();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function destroy(string $sessionId): bool
    {
        $this->prepareKey($sessionId) |> $this->connection->delete(...);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Note: Memcached handles expiration internally, so no explicit garbage collection is
     * performed. This returns `1` to satisfy the session handler API.
     */
    #[Override]
    public function gc(int $expires): false|int
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     *
     * @throws SessionException If the connection fails.
     */
    #[Override]
    public function open(string $path, string $name): bool
    {
        try {
            $this->connection = new Memcached();

            $this->connection->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

            $this->connection->addServer(
                $this->config['host'],
                (int) $this->config['port'],
                $this->config['weight']
            );

            if (!$this->getStats()) {
                throw new SessionException('Memcache session connection failed.');
            }
        } catch (MemcachedException $e) {
            throw new SessionException(sprintf(
                'Memcache session connection error: %s',
                $e->getMessage()
            ), previous: $e);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function read(string $sessionId): false|string
    {
        $value = $this->prepareKey($sessionId) |> $this->connection->get(...);

        if ($this->connection->getResultCode() === Memcached::RES_NOTFOUND) {
            return '';
        }

        return (string) $value;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function write(string $sessionId, string $data): bool
    {
        if (!$sessionId) {
            return false;
        }

        $key = $this->prepareKey($sessionId);

        if (!$this->connection->set($key, $data, $this->config['expires'])) {
            return false;
        }

        return true;
    }

    /**
     * Returns memcached stats.
     *
     * @return array<string, mixed>|null The memcached stats for the configured server.
     */
    protected function getStats(): array|null
    {
        $stats = $this->connection->getStats();

        $server = $this->config['host'].':'.$this->config['port'];

        return $stats[$server] ?? null;
    }
}
