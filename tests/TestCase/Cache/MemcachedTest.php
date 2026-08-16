<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\Memcached\MemcachedCacher;
use Fyre\Core\Container;
use Override;
use PHPUnit\Framework\TestCase;
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

final class MemcachedTest extends TestCase
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

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

        $this->assertArraysAreIdentical(
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

    public function testExpiredValue(): void
    {
        $this->cacher->set('test', 'value', 1);

        sleep(2);

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Memcache cache connection failed.');

        $this->cacheManager->build([
            'className' => MemcachedCacher::class,
            'port' => 1,
        ]);
    }

    public function testSetLongExpiry(): void
    {
        $this->assertTrue(
            $this->cacher->set('test', 'value', 2_592_001)
        );

        $this->assertSame(
            'value',
            $this->cacher->get('test')
        );
    }

    public function testSetMultipleLongExpiry(): void
    {
        $values = [
            'test1' => 'value1',
            'test2' => 'value2',
        ];

        $this->assertTrue(
            $this->cacher->setMultiple($values, 2_592_001)
        );

        $this->assertSame(
            $values,
            $this->cacher->getMultiple(['test1', 'test2'])
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cacheManager = new Container()
            ->use(CacheManager::class);

        $this->cacher = $this->cacheManager->build([
            'className' => MemcachedCacher::class,
            'host' => getenv('MEMCACHED_HOST'),
            'port' => getenv('MEMCACHED_PORT'),
            'prefix' => 'prefix.',
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacher->clear();
    }
}
