<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\MemcachedCacher;
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

final class MemcachedTest extends TestCase
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
                '[class]' => MemcachedCacher::class,
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'host' => '[*****]',
                    'port' => '[*****]',
                    'weight' => 1,
                    'className' => MemcachedCacher::class,
                ],
                'connection' => '[Memcached]',
            ],
            $data
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Memcache cache connection failed.');

        new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => MemcachedCacher::class,
                'port' => 1234,
            ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cache = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => MemcachedCacher::class,
                'host' => getenv('MEMCACHED_HOST'),
                'port' => getenv('MEMCACHED_PORT'),
                'prefix' => 'prefix.',
            ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cache->clear();
    }
}
