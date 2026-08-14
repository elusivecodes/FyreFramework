<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Closure;
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

    public function testExpiredValue(): void
    {
        Closure::bind(function(): void {
            $this->setValue('prefix.test', 'value', -1);
        }, $this->cacher, ArrayCacher::class)();

        $this->assertNull(
            $this->cacher->get('test')
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

        $data = $cacher->__debugInfo();

        $this->assertNull(
            $data['cache']['__tag__.users']['expires']
        );

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
