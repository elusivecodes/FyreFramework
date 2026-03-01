<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\FileCacher;
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

use function mkdir;
use function rmdir;
use function unlink;

final class FileTest extends TestCase
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
                '[class]' => FileCacher::class,
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'path' => '[*****]',
                    'mode' => 0640,
                    'className' => FileCacher::class,
                ],
                'path' => '[*****]',
            ],
            $data
        );
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir('cache');

        $this->cache = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.',
            ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('cache/prefix.test');
        @unlink('cache/prefix.test1');
        @unlink('cache/prefix.test2');
        @rmdir('cache');
    }
}
