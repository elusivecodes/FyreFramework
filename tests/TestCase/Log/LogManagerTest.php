<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Log\Handlers\ArrayLogger;
use Fyre\Log\Logger;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class LogManagerTest extends TestCase
{
    protected LogManager $logManager;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            ArrayLogger::class,
            $this->logManager->build([
                'className' => ArrayLogger::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->logManager->build([
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
        $config = $this->logManager->getConfig();

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'default' => [
                    'className' => ArrayLogger::class,
                    'levels' => ['warning', 'notice', 'info', 'debug'],
                ],
                'error' => [
                    'className' => ArrayLogger::class,
                    'levels' => ['emergency', 'alert', 'critical', 'error'],
                ],
            ],
            $config
        );
    }

    public function testGetConfigKey(): void
    {
        $config = $this->logManager->getConfig('error');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => ArrayLogger::class,
                'levels' => ['emergency', 'alert', 'critical', 'error'],
            ],
            $config
        );
    }

    public function testIsLoaded(): void
    {
        $this->logManager->use();

        $this->assertTrue(
            $this->logManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->logManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->logManager->use('error');

        $this->assertTrue(
            $this->logManager->isLoaded('error')
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
            $this->logManager,
            $this->logManager->setConfig('test', [
                'className' => ArrayLogger::class,
                'levels' => ['debug'],
            ])
        );

        $config = $this->logManager->getConfig('test');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => ArrayLogger::class,
                'levels' => ['debug'],
            ],
            $config
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Log config `default` already exists.');

        $this->logManager->setConfig('default', [
            'className' => ArrayLogger::class,
            'levels' => ['debug'],
        ]);
    }

    public function testUnload(): void
    {
        $this->logManager->use();

        $this->assertSame(
            $this->logManager,
            $this->logManager->unload()
        );

        $this->assertFalse(
            $this->logManager->isLoaded()
        );
        $this->assertFalse(
            $this->logManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->logManager,
            $this->logManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->logManager->use('error');

        $this->assertSame(
            $this->logManager,
            $this->logManager->unload('error')
        );

        $this->assertFalse(
            $this->logManager->isLoaded('error')
        );
        $this->assertFalse(
            $this->logManager->hasConfig('error')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->logManager->use();
        $handler2 = $this->logManager->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            ArrayLogger::class,
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
                'className' => ArrayLogger::class,
                'levels' => ['warning', 'notice', 'info', 'debug'],
            ],
            'error' => [
                'className' => ArrayLogger::class,
                'levels' => ['emergency', 'alert', 'critical', 'error'],
            ],
        ]);
        $this->logManager = $container->use(LogManager::class);
    }
}
