<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\InvalidArgumentException;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Cache\Handlers\NullCacher;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function mkdir;
use function rmdir;

final class CacheManagerTest extends TestCase
{
    protected CacheManager $cache;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            FileCacher::class,
            $this->cache->build([
                'className' => FileCacher::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cacher `Invalid` must extend `Fyre\Cache\Cacher`.');

        $this->cache->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CacheManager::class)
        );
    }

    public function testDisable(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->disable()
        );

        $this->assertFalse(
            $this->cache->isEnabled()
        );

        $this->assertInstanceOf(
            NullCacher::class,
            $this->cache->use()
        );
    }

    public function testEnable(): void
    {
        $this->cache->disable();

        $this->assertSame(
            $this->cache,
            $this->cache->enable()
        );

        $this->assertTrue(
            $this->cache->isEnabled()
        );

        $this->assertInstanceOf(
            FileCacher::class,
            $this->cache->use()
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => FileCacher::class,
                    'path' => 'cache',
                    'prefix' => 'prefix.',
                ],
                'data' => [
                    'className' => FileCacher::class,
                    'path' => 'cache',
                    'prefix' => 'data.',
                ],
            ],
            $this->cache->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'data.',
            ],
            $this->cache->getConfig('data')
        );
    }

    public function testIsLoaded(): void
    {
        $this->cache->use();

        $this->assertTrue(
            $this->cache->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->cache->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->cache->use('data');

        $this->assertTrue(
            $this->cache->isLoaded('data')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Cacher::class)
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->setConfig('test', [
                'className' => FileCacher::class,
            ])
        );

        $this->assertSame(
            [
                'className' => FileCacher::class,
            ],
            $this->cache->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache config `default` already exists.');

        $this->cache->setConfig('default', [
            'className' => FileCacher::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->cache->use();

        $this->assertSame(
            $this->cache,
            $this->cache->unload()
        );

        $this->assertFalse(
            $this->cache->isLoaded()
        );
        $this->assertFalse(
            $this->cache->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->cache->use('data');

        $this->assertSame(
            $this->cache,
            $this->cache->unload('data')
        );

        $this->assertFalse(
            $this->cache->isLoaded('data')
        );
        $this->assertFalse(
            $this->cache->hasConfig('data')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->cache->use();
        $handler2 = $this->cache->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileCacher::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Cache', [
            'default' => [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.',
            ],
            'data' => [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'data.',
            ],
        ]);
        $this->cache = $container->use(CacheManager::class);

        @mkdir('cache');
    }

    #[Override]
    protected function tearDown(): void
    {
        @rmdir('cache');
    }
}
