<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\Array\ArrayCacher;
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

use function sleep;

final class ArrayTest extends TestCase
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

    protected Cacher $cacher;

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

        $this->assertSame(
            [
                '[class]' => ArrayCacher::class,
                'cache' => [],
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'className' => ArrayCacher::class,
                ],
                'locks' => [],
            ],
            $data
        );
    }

    public function testTaggedInvalidationDoesNotExpire(): void
    {
        $cacher = new ArrayCacher([
            'expire' => 1,
        ]);

        $usersCache = $cacher->tags('users');
        $usersCache->set('user.1', 'value', 30);

        $cacher->invalidateTag('users');

        sleep(2);

        $this->assertNull(
            $usersCache->get('user.1')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cacher = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => ArrayCacher::class,
                'prefix' => 'prefix.',
            ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacher->delete('test');
    }
}
