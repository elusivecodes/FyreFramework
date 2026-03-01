<?php
declare(strict_types=1);

namespace Fyre\Queue\Handlers;

use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\Core\Container;
use Fyre\Queue\Exceptions\QueueException;
use Fyre\Queue\Message;
use Fyre\Queue\Queue;
use InvalidArgumentException;
use Override;
use Redis;
use RedisException;

use function array_shift;
use function count;
use function explode;
use function in_array;
use function serialize;
use function sprintf;
use function time;
use function unserialize;

/**
 * Queue implementation backed by Redis.
 *
 * Uses a Redis list for queued messages, a sorted set for delayed messages, and a hash for
 * uniqueness checks (when enabled).
 */
class RedisQueue extends Queue
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
     * Constructs a RedisQueue.
     *
     * @param Container $container The Container.
     * @param array<string, mixed> $options The queue options.
     *
     * @throws QueueException If the connection fails.
     * @throws InvalidArgumentException If the connection database is not valid.
     */
    public function __construct(Container $container, array $options = [])
    {
        parent::__construct($container, $options);

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
                throw new QueueException('Redis queue connection failed.');
            }

            if ($this->config['password'] && !$this->connection->auth($this->config['password'])) {
                throw new QueueException('Redis queue authentication failed.');
            }

            if ($this->config['database'] && !$this->connection->select($this->config['database'])) {
                throw new InvalidArgumentException(sprintf(
                    'Redis queue database `%s` is not valid.',
                    $this->config['database']
                ));
            }

        } catch (RedisException $e) {
            throw new QueueException(sprintf(
                'Redis queue connection error: %s',
                $e->getMessage()
            ), previous: $e);
        }
    }

    /**
     * Releases the Redis connection.
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function clear(string $queue = self::DEFAULT): void
    {
        $this->connection->del(static::prepareKey($queue));
        $this->connection->del(static::prepareKey($queue, 'unique'));
        $this->connection->zRemRangeByScore(static::prepareKey($queue, 'delayed'), '-inf', '+inf');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function complete(Message $message): void
    {
        $queue = $message->getQueue();

        $this->connection->incrBy(static::prepareKey($queue, 'completed'), 1);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function fail(Message $message): bool
    {
        $queue = $message->getQueue();

        $this->connection->incrBy(static::prepareKey($queue, 'failed'), 1);

        if (!$message->shouldRetry()) {
            return false;
        }

        return $this->push($message);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function pop(string $queue = self::DEFAULT): Message|null
    {
        // check for delayed messages
        $this->connection->watch(static::prepareKey($queue, 'delayed'));

        $itemsReady = $this->connection->zRangeByScore(static::prepareKey($queue, 'delayed'), '0', (string) time());

        if ($itemsReady !== []) {
            $this->connection->multi();

            foreach ($itemsReady as $data) {
                $this->connection->lPush(static::prepareKey($queue), $data);
            }

            $this->connection->zRem(static::prepareKey($queue, 'delayed'), ...$itemsReady);
            $this->connection->incrBy(static::prepareKey($queue, 'total'), count($itemsReady));
            $this->connection->exec();
        } else {
            $this->connection->unwatch();
        }

        // get the next message
        $data = $this->connection->rPop(static::prepareKey($queue));

        if (!$data) {
            return null;
        }

        $message = unserialize($data);

        if ($message->isUnique()) {
            $this->connection->hDel(static::prepareKey($queue, 'unique'), $message->getHash());
        }

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function push(Message $message): bool
    {
        if ($message->isExpired()) {
            return false;
        }

        $queue = $message->getQueue();

        if ($message->isUnique()) {
            $uniqueKey = static::prepareKey($queue, 'unique');
            $messageHash = $message->getHash();

            if ($this->connection->hExists($uniqueKey, $messageHash)) {
                return false;
            }

            $this->connection->hSet($uniqueKey, $messageHash, 1);
        }

        $data = serialize($message);

        if (!$message->isReady()) {
            $this->connection->zAdd(static::prepareKey($queue, 'delayed'), (float) $message->getAfter(), $data);
        } else {
            $this->connection->lPush(static::prepareKey($queue), $data);
            $this->connection->incrBy(static::prepareKey($queue, 'total'), 1);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function queues(): array
    {
        $keys = $this->connection->keys(static::prepareKey('*'));

        $queues = [];

        foreach ($keys as $key) {
            $values = explode(':', $key);

            if (count($values) > 1) {
                array_shift($values);
            }

            if ($values[0] && !in_array($values[0], $queues, true)) {
                $queues[] = $values[0];
            }
        }

        return $queues;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function reset(string $queue = self::DEFAULT): void
    {
        $this->connection->del(static::prepareKey($queue, 'completed'));
        $this->connection->del(static::prepareKey($queue, 'failed'));
        $this->connection->del(static::prepareKey($queue, 'total'));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function stats(string $queue = self::DEFAULT): array
    {
        return [
            'queued' => (int) $this->connection->lLen(static::prepareKey($queue)),
            'delayed' => (int) $this->connection->zCount(static::prepareKey($queue, 'delayed'), '-inf', '+inf'),
            'completed' => (int) $this->connection->get(static::prepareKey($queue, 'completed')),
            'failed' => (int) $this->connection->get(static::prepareKey($queue, 'failed')),
            'total' => (int) $this->connection->get(static::prepareKey($queue, 'total')),
        ];
    }

    /**
     * Returns the key for a queue with optional suffix.
     *
     * @param string $queue The queue name.
     * @param string $suffix The key suffix.
     * @return string The key.
     */
    protected static function prepareKey(string $queue, string $suffix = ''): string
    {
        return 'queue:'.$queue.($suffix ? ':'.$suffix : '');
    }
}
