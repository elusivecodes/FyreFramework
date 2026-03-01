<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use Fyre\Http\Session\Exceptions\SessionException;
use Fyre\Http\Session\SessionHandler;
use InvalidArgumentException;
use Override;
use Redis;
use RedisException;

use function sprintf;

/**
 * Stores session data in Redis with an expiration time (`expires`) using `SETEX`.
 */
class RedisSessionHandler extends SessionHandler
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'prefix' => 'session:',
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => null,
        'timeout' => 0,
        'persist' => true,
        'tls' => false,
        'ssl' => [
            'key' => null,
            'cert' => null,
            'ca' => null,
        ],
    ];

    protected Redis $connection;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function close(): bool
    {
        $this->connection->close();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function destroy(string $sessionId): bool
    {
        $this->prepareKey($sessionId) |> $this->connection->del(...);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Note: Redis key TTL handles expiration internally, so no explicit garbage collection is
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
     * @throws InvalidArgumentException If the connection database is not valid.
     */
    #[Override]
    public function open(string $path, string $name): bool
    {
        try {
            $this->connection = new Redis();

            $tls = $this->config['tls'] ? 'tls://' : '';

            if (!$this->connection->connect(
                $tls.$this->config['host'],
                (int) $this->config['port'],
                (int) $this->config['timeout'],
                null,
                0,
                0,
                [
                    'ssl' => [
                        'local_pk' => $this->config['ssl']['key'] ?? null,
                        'local_cert' => $this->config['ssl']['cert'] ?? null,
                        'cafile' => $this->config['ssl']['ca'] ?? null,
                    ],
                ],
            )) {
                throw new SessionException('Redis session connection failed.');
            }

            if ($this->config['password'] && !$this->connection->auth($this->config['password'])) {
                throw new SessionException('Redis session authentication failed.');
            }

            if ($this->config['database'] !== null && !$this->connection->select((int) $this->config['database'])) {
                throw new InvalidArgumentException(sprintf(
                    'Redis session database `%s` is not valid.',
                    $this->config['database']
                ));
            }

        } catch (RedisException $e) {
            throw new SessionException(sprintf(
                'Redis session connection error: %s',
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
        return (string) ($this->prepareKey($sessionId) |> $this->connection->get(...));
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

        if (!$this->connection->setEx($key, $this->config['expires'], $data)) {
            return false;
        }

        return true;
    }
}
