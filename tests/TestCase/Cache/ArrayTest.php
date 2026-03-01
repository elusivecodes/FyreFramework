<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\ArrayCacher;
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

final class ArrayTest extends TestCase
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
                '[class]' => ArrayCacher::class,
                'cache' => [],
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'className' => ArrayCacher::class,
                ],
            ],
            $data
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cache = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => ArrayCacher::class,
                'prefix' => 'prefix.',
            ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cache->delete('test');
    }
}
