<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Exceptions\InvalidArgumentException;
use Fyre\Core\Attributes\SensitivePropertyArray;
use Override;
use Redis;
use RedisException;

use function array_map;
use function count;
use function gettype;
use function iterator_to_array;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * Caches values using Redis.
 */
class RedisCacher extends Cacher
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => null,
        'timeout' => 0,
        'persist' => true,
        'flushDatabase' => false,
        'tls' => false,
        'ssl' => [
            'key' => null,
            'cert' => null,
            'ca' => null,
        ],
    ];

    /**
     * @var array<string, mixed>
     */
    #[Override]
    #[SensitivePropertyArray([
        'host',
        'password',
        'port',
        'database',
        'ssl' => [
            'key',
            'cert',
            'ca',
        ],
    ])]
    protected array $config;

    protected Redis $connection;

    /**
     * Constructs a RedisCacher.
     *
     * @param array<string, mixed> $options The Cacher options.
     *
     * @throws CacheException If the connection fails.
     * @throws InvalidArgumentException If the connection database is not valid.
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        try {
            $this->connection = new Redis();

            $tls = $this->config['tls'] ? 'tls://' : '';

            if (!$this->connection->connect(
                $tls.$this->config['host'],
                (int) $this->config['port'],
                (int) $this->config['timeout'],
                $this->config['persist'] ?
                    ($this->config['port'].$this->config['timeout'].$this->config['database']) :
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
                throw new CacheException('Redis cache connection failed.');
            }

            if ($this->config['password'] && !$this->connection->auth($this->config['password'])) {
                throw new CacheException('Redis cache authentication failed.');
            }

            if ($this->config['database'] && !$this->connection->select($this->config['database'])) {
                throw new InvalidArgumentException(sprintf(
                    'Redis cache database `%s` is not valid.',
                    $this->config['database']
                ));
            }

        } catch (RedisException $e) {
            throw new CacheException(sprintf(
                'Redis cache connection error: %s',
                $e->getMessage()
            ), previous: $e);
        }
    }

    /**
     * Releases the Redis connection.
     */
    public function __destruct()
    {
        if (!$this->config['persist']) {
            $this->connection->close();
        }
    }

    /**
     * {@inheritDoc}
     *
     * Note: Clearing without a prefix requires explicit opt-in because it flushes the entire Redis database.
     *
     * @throws CacheException If clearing without a prefix is not allowed.
     */
    #[Override]
    public function clear(): bool
    {
        if (!$this->config['prefix']) {
            if (!$this->config['flushDatabase']) {
                throw new CacheException(
                    'Redis cache clear requires a non-empty prefix or flushDatabase enabled.'
                );
            }

            return $this->connection->flushDB(false);
        }

        $iterator = null;
        $pattern = $this->config['prefix'].'*';

        while (($keys = $this->connection->scan($iterator, $pattern, 50)) !== false) {
            if ($keys && $this->connection->unlink($keys) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $key): bool
    {
        return $this->prepareKey($key) |> $this->connection->del(...) > 0;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = array_map(
            $this->prepareKey(...),
            iterator_to_array($keys)
        );

        return $this->connection->del($keys) >= count($keys);
    }

    /**
     * {@inheritDoc}
     *
     * Note: Values are stored in a hash with a `type` and `value` field so the original type can be restored.
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->prepareKey($key);

        $data = $this->connection->hMGet($key, ['type', 'value']);

        switch ($data['type']) {
            case 'array':
            case 'object':
                return unserialize($data['value']);
            case 'boolean':
                return (bool) $data['value'];
            case 'double':
                return (float) $data['value'];
            case 'integer':
                return (int) $data['value'];
            case 'string':
                return (string) $data['value'];
            case 'NULL':
                return null;
            default:
                return $default;
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function has(string $key): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->hExists($key, 'value');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function increment(string $key, int $amount = 1): false|int
    {
        $key = $this->prepareKey($key);

        if (!$this->connection->hExists($key, 'value')) {
            $this->connection->hSetNx($key, 'type', 'integer');
            $this->connection->hSetNx($key, 'value', '0');
        }

        return $this->connection->hIncrBy($key, 'value', $amount);
    }

    /**
     * {@inheritDoc}
     *
     * Note: Only scalar types, arrays, objects, and null are supported.
     */
    #[Override]
    public function set(string $key, mixed $value, DateInterval|int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);
        $expires = $this->getExpires($expire);

        $type = gettype($value);

        switch ($type) {
            case 'array':
            case 'object':
                $value = serialize($value);
                break;
            case 'boolean':
            case 'double':
            case 'integer':
            case 'string':
            case 'NULL':
                break;
            default:
                return false;
        }

        $this->connection->hMSet($key, ['type' => $type, 'value' => $value]);

        if ($expires !== null) {
            $this->connection->expire($key, $expires);
        }

        return true;
    }
}
