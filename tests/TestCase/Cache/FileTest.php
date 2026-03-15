<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Cache\TaggedCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\Cache\Cacher\DecrementTestTrait;
use Tests\TestCase\Cache\Cacher\DeleteTestTrait;
use Tests\TestCase\Cache\Cacher\EmptyTestTrait;
use Tests\TestCase\Cache\Cacher\GetSetTestTrait;
use Tests\TestCase\Cache\Cacher\HasTestTrait;
use Tests\TestCase\Cache\Cacher\IncrementTestTrait;
use Tests\TestCase\Cache\Cacher\RememberTestTrait;
use Tests\TestCase\Cache\Cacher\TagsTestTrait;

use function class_uses;
use function mkdir;
use function rmdir;

final class FileTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use GetSetTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RememberTestTrait;
    use TagsTestTrait;

    protected Cacher $cacher;

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

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

        $this->assertContains(
            DebugTrait::class,
            class_uses(TaggedCacher::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir('cache');

        $this->cacher = new Container()
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
        $this->cacher->clear();
        @rmdir('cache');
    }
}
