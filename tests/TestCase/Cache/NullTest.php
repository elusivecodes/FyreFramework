<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\NullCacher;
use Fyre\Core\Container;
use Override;
use PHPUnit\Framework\TestCase;

final class NullTest extends TestCase
{
    protected Cacher $cacher;

    public function testClear(): void
    {
        $this->assertTrue(
            $this->cacher->clear()
        );
    }

    public function testDebug(): void
    {
        $this->assertSame(
            [
                '[class]' => NullCacher::class,
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'className' => NullCacher::class,
                ],
            ],
            $this->cacher->__debugInfo()
        );
    }

    public function testDelete(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertTrue(
            $this->cacher->delete('test')
        );

        $this->assertTrue(
            $this->cacher->delete('missing')
        );
    }

    public function testGet(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertNull(
            $this->cacher->get('test')
        );

        $this->assertSame(
            'default',
            $this->cacher->get('test', 'default')
        );
    }

    public function testHas(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertFalse(
            $this->cacher->has('test')
        );
    }

    public function testIncrement(): void
    {
        $this->assertSame(
            5,
            $this->cacher->increment('test', 5)
        );

        $this->assertSame(
            -5,
            $this->cacher->decrement('test', 5)
        );

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testInvalidKeys(): void
    {
        $this->assertTrue(
            $this->cacher->set('test/', 'value')
        );

        $this->assertSame(
            'default',
            $this->cacher->get('test/', 'default')
        );

        $this->assertTrue(
            $this->cacher->delete('test/')
        );
    }

    public function testRemember(): void
    {
        $calls = 0;

        $this->assertSame(
            1,
            $this->cacher->remember('test', static function() use (&$calls): int {
                $calls++;

                return $calls;
            })
        );

        $this->assertSame(
            2,
            $this->cacher->remember('test', static function() use (&$calls): int {
                $calls++;

                return $calls;
            })
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->cacher = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => NullCacher::class,
                'prefix' => 'prefix.',
            ]);
    }
}
