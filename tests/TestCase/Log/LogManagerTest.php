<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\Logger;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function mkdir;
use function rmdir;

final class LogManagerTest extends TestCase
{
    protected LogManager $log;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            FileLogger::class,
            $this->log->build([
                'className' => FileLogger::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->log->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(LogManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Logger::class)
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => FileLogger::class,
                    'levels' => ['warning', 'notice', 'info', 'debug'],
                    'path' => 'log',
                    'suffix' => '',
                ],
                'error' => [
                    'className' => FileLogger::class,
                    'levels' => ['emergency', 'alert', 'critical', 'error'],
                    'path' => 'error',
                    'suffix' => '',
                ],
            ],
            $this->log->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => FileLogger::class,
                'levels' => ['emergency', 'alert', 'critical', 'error'],
                'path' => 'error',
                'suffix' => '',
            ],
            $this->log->getConfig('error')
        );
    }

    public function testIsLoaded(): void
    {
        $this->log->use();

        $this->assertTrue(
            $this->log->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->log->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->log->use('error');

        $this->assertTrue(
            $this->log->isLoaded('error')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Logger::class)
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->log,
            $this->log->setConfig('test', [
                'className' => FileLogger::class,
                'path' => 'log',
            ])
        );

        $this->assertSame(
            [
                'className' => FileLogger::class,
                'path' => 'log',
            ],
            $this->log->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log config `default` already exists.');

        $this->log->setConfig('default', [
            'className' => FileLogger::class,
            'path' => 'log',
        ]);
    }

    public function testUnload(): void
    {
        $this->log->use();

        $this->assertSame(
            $this->log,
            $this->log->unload()
        );

        $this->assertFalse(
            $this->log->isLoaded()
        );
        $this->assertFalse(
            $this->log->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->log,
            $this->log->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->log->use('error');

        $this->assertSame(
            $this->log,
            $this->log->unload('error')
        );

        $this->assertFalse(
            $this->log->isLoaded('error')
        );
        $this->assertFalse(
            $this->log->hasConfig('error')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->log->use();
        $handler2 = $this->log->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileLogger::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Log', [
            'default' => [
                'className' => FileLogger::class,
                'levels' => ['warning', 'notice', 'info', 'debug'],
                'path' => 'log',
                'suffix' => '',
            ],
            'error' => [
                'className' => FileLogger::class,
                'levels' => ['emergency', 'alert', 'critical', 'error'],
                'path' => 'error',
                'suffix' => '',
            ],
        ]);
        $this->log = $container->use(LogManager::class);

        @mkdir('log');
        @mkdir('error');
    }

    #[Override]
    protected function tearDown(): void
    {
        @rmdir('log');
        @rmdir('error');
    }
}
