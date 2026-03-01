<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\RedisCacher;
use Fyre\Core\Container;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\Cache\Cacher\DecrementTestTrait;
use Tests\TestCase\Cache\Cacher\DeleteTestTrait;
use Tests\TestCase\Cache\Cacher\EmptyTestTrait;
use Tests\TestCase\Cache\Cacher\GetSetTestTrait;
use Tests\TestCase\Cache\Cacher\HasTestTrait;
use Tests\TestCase\Cache\Cacher\IncrementTestTrait;
use Tests\TestCase\Cache\Cacher\RememberTestTrait;

use function getenv;

final class RedisTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use GetSetTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RememberTestTrait;

    protected Cacher $cache;

    public function testDebug(): void
    {
        $data = $this->cache->__debugInfo();

        $this->assertSame(
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

    public function testInvalidAuth(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageMatches('/^Redis cache connection error: ERR AUTH/');

        new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => RedisCacher::class,
                'host' => getenv('REDIS_HOST'),
                'password' => 'invalid',
            ]);
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Redis cache connection error: Connection refused');

        new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => RedisCacher::class,
                'port' => 1234,
            ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cache = new Container()
            ->use(CacheManager::class)
            ->build([
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
        $this->cache->clear();
    }
}
