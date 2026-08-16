<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\InvalidArgumentException;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Cache\Handlers\Null\NullCacher;
use Fyre\Cache\Lock;
use Fyre\Cache\TaggedCacher;
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
    protected CacheManager $cacheManager;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            FileCacher::class,
            $this->cacheManager->build([
                'className' => FileCacher::class,
            ])
        );
    }

    public function testBuildDisabled(): void
    {
        $this->cacheManager->disable();

        $handler1 = $this->cacheManager->build([
            'className' => FileCacher::class,
        ]);
        $handler2 = $this->cacheManager->build([
            'className' => FileCacher::class,
        ]);

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            NullCacher::class,
            $handler1
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cacher `Invalid` must extend `Fyre\Cache\Cacher`.');

        $this->cacheManager->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CacheManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(TaggedCacher::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Lock::class)
        );
    }

    public function testDisable(): void
    {
        $this->assertSame(
            $this->cacheManager,
            $this->cacheManager->disable()
        );

        $this->assertFalse(
            $this->cacheManager->isEnabled()
        );

        $this->assertInstanceOf(
            NullCacher::class,
            $this->cacheManager->use()
        );
    }

    public function testEnable(): void
    {
        $this->cacheManager->disable();

        $this->assertSame(
            $this->cacheManager,
            $this->cacheManager->enable()
        );

        $this->assertTrue(
            $this->cacheManager->isEnabled()
        );

        $this->assertInstanceOf(
            FileCacher::class,
            $this->cacheManager->use()
        );
    }

    public function testGetConfig(): void
    {
        $config = $this->cacheManager->getConfig();

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
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
            $config
        );
    }

    public function testGetConfigKey(): void
    {
        $config = $this->cacheManager->getConfig('data');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'data.',
            ],
            $config
        );
    }

    public function testIsLoaded(): void
    {
        $this->cacheManager->use();

        $this->assertTrue(
            $this->cacheManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->cacheManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->cacheManager->use('data');

        $this->assertTrue(
            $this->cacheManager->isLoaded('data')
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
            $this->cacheManager,
            $this->cacheManager->setConfig('test', [
                'className' => FileCacher::class,
            ])
        );

        $config = $this->cacheManager->getConfig('test');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => FileCacher::class,
            ],
            $config
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cache config `default` already exists.');

        $this->cacheManager->setConfig('default', [
            'className' => FileCacher::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->cacheManager->use();

        $this->assertSame(
            $this->cacheManager,
            $this->cacheManager->unload()
        );

        $this->assertFalse(
            $this->cacheManager->isLoaded()
        );
        $this->assertFalse(
            $this->cacheManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->cacheManager,
            $this->cacheManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->cacheManager->use('data');

        $this->assertSame(
            $this->cacheManager,
            $this->cacheManager->unload('data')
        );

        $this->assertFalse(
            $this->cacheManager->isLoaded('data')
        );
        $this->assertFalse(
            $this->cacheManager->hasConfig('data')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->cacheManager->use();
        $handler2 = $this->cacheManager->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileCacher::class,
            $handler1
        );
    }

    public function testUseDisabledInvalid(): void
    {
        $this->cacheManager->disable();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cacher `` must extend `Fyre\Cache\Cacher`.');

        $this->cacheManager->use('invalid');
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
        $this->cacheManager = $container->use(CacheManager::class);

        @mkdir('cache');
    }

    #[Override]
    protected function tearDown(): void
    {
        @rmdir('cache');
    }
}
