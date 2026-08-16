<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Closure;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\Redis\RedisCacher;
use Fyre\Core\Container;
use Override;
use PHPUnit\Framework\TestCase;
use Redis;
use Tests\TestCase\Cache\Cacher\DecrementTestTrait;
use Tests\TestCase\Cache\Cacher\DeleteTestTrait;
use Tests\TestCase\Cache\Cacher\EmptyTestTrait;
use Tests\TestCase\Cache\Cacher\GetSetTestTrait;
use Tests\TestCase\Cache\Cacher\HasTestTrait;
use Tests\TestCase\Cache\Cacher\IncrementTestTrait;
use Tests\TestCase\Cache\Cacher\LockTestTrait;
use Tests\TestCase\Cache\Cacher\RememberTestTrait;
use Tests\TestCase\Cache\Cacher\TagsTestTrait;

use function getenv;
use function sleep;

final class RedisTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use GetSetTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use LockTestTrait;
    use RememberTestTrait;
    use TagsTestTrait;

    protected CacheManager $cacheManager;

    protected Cacher $cacher;

    public function testClearWithoutPrefixAllowsFlushWhenEnabled(): void
    {
        $cache = $this->cacheManager->build([
            'className' => RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => getenv('REDIS_PASSWORD'),
            'database' => getenv('REDIS_DATABASE'),
            'port' => getenv('REDIS_PORT'),
            'flushDatabase' => true,
        ]);

        $this->assertTrue($cache->clear());
    }

    public function testClearWithoutPrefixThrows(): void
    {
        $cache = $this->cacheManager->build([
            'className' => RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => getenv('REDIS_PASSWORD'),
            'database' => getenv('REDIS_DATABASE'),
            'port' => getenv('REDIS_PORT'),
        ]);

        $this->expectException(CacheException::class);
        $this->expectExceptionMessageIs('Redis cache clear requires a non-empty prefix or flushDatabase enabled.');

        $cache->clear();
    }

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

        $this->assertArraysAreIdentical(
            [
                '[class]' => RedisCacher::class,
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'host' => '[*****]',
                    'password' => '',
                    'port' => '[*****]',
                    'database' => '',
                    'timeout' => 0,
                    'persist' => true,
                    'flushDatabase' => false,
                    'tls' => false,
                    'ssl' => [
                        'key' => null,
                        'cert' => null,
                        'ca' => null,
                    ],
                    'className' => RedisCacher::class,
                ],
                'connection' => '[Redis]',
            ],
            $data
        );
    }

    public function testExpiredValue(): void
    {
        $this->cacher->set('test', 'value', 1);

        sleep(2);

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testInvalidAuth(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageMatches('/^Redis cache connection error: /');

        $this->cacheManager->build([
            'className' => RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => 'invalid',
        ]);
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageMatches(
            '/^Redis cache connection error: (?:Connection refused|Connection timed out)\z/'
        );

        $this->cacheManager->build([
            'className' => RedisCacher::class,
            'port' => 1,
            'timeout' => 1,
        ]);
    }

    public function testSetClearsExpiry(): void
    {
        $this->cacher->set('test', 'value', 1);
        $this->cacher->set('test', 'new');

        $connection = Closure::bind(function(): Redis {
            /** @var RedisCacher $this */
            return $this->connection;
        }, $this->cacher, RedisCacher::class)();

        $this->assertSame(
            -1,
            $connection->ttl('prefix.test')
        );

        $this->assertSame(
            'new',
            $this->cacher->get('test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cacheManager = new Container()
            ->use(CacheManager::class);

        $this->cacher = $this->cacheManager->build([
            'className' => RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => getenv('REDIS_PASSWORD'),
            'database' => getenv('REDIS_DATABASE'),
            'port' => getenv('REDIS_PORT'),
            'prefix' => 'prefix.',
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacher->clear();
    }
}
