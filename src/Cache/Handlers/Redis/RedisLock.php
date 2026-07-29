<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\Redis;

use Fyre\Cache\Lock;
use Redis;

/**
 * Provides owner-token locking using Redis.
 */
class RedisLock extends Lock
{
    /**
     * Constructs a RedisLock.
     *
     * @param Redis $connection The Redis connection.
     * @param string $key The lock key.
     * @param int $expires The lock lifetime in seconds.
     */
    public function __construct(
        protected Redis $connection,
        string $key,
        int $expires = 30
    ) {
        parent::__construct($key, $expires);
    }

    /**
     * {@inheritDoc}
     */
    protected function acquireLock(): bool
    {
        return $this->connection->set($this->key, $this->owner, [
            'nx',
            'ex' => $this->expires,
        ]) === true;
    }

    /**
     * {@inheritDoc}
     */
    protected function refreshLock(): bool
    {
        $result = $this->connection->eval(
            <<<'LUA'
                if redis.call('get', KEYS[1]) ~= ARGV[1] then
                    return 0
                end

                return redis.call('pexpire', KEYS[1], ARGV[2])
                LUA,
            [
                $this->key,
                $this->owner,
                (string) ($this->expires * 1000),
            ],
            1
        );

        return (int) $result === 1;
    }

    /**
     * {@inheritDoc}
     */
    protected function releaseLock(): bool
    {
        $result = $this->connection->eval(
            <<<'LUA'
                if redis.call('get', KEYS[1]) ~= ARGV[1] then
                    return 0
                end

                return redis.call('del', KEYS[1])
                LUA,
            [
                $this->key,
                $this->owner,
            ],
            1
        );

        return (int) $result === 1;
    }
}
