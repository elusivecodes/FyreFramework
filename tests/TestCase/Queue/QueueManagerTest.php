<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Queue\TestQueue;

use function class_uses;

final class QueueManagerTest extends TestCase
{
    protected QueueManager $queueManager;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            TestQueue::class,
            $this->queueManager->build([
                'className' => TestQueue::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Queue `Invalid` must extend `Fyre\Queue\Queue`.');

        $this->queueManager->build([
            'className' => 'Invalid',
        ]);
    }

    public function testClear(): void
    {
        $this->queueManager->use();

        $this->queueManager->clear();

        $this->assertFalse($this->queueManager->isLoaded());
        $this->assertFalse($this->queueManager->hasConfig());
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(QueueManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Queue::class)
        );
    }

    public function testGetConfig(): void
    {
        $config = $this->queueManager->getConfig();

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'default' => [
                    'className' => TestQueue::class,
                    'queues' => ['default'],
                ],
                'other' => [
                    'className' => TestQueue::class,
                    'queues' => ['other'],
                ],
            ],
            $config
        );
    }

    public function testGetConfigEmptyKey(): void
    {
        $config = [
            'className' => TestQueue::class,
        ];

        $this->queueManager->setConfig('', $config);

        $this->assertSame(
            $config,
            $this->queueManager->getConfig('')
        );
    }

    public function testGetConfigKey(): void
    {
        $config = $this->queueManager->getConfig('other');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => TestQueue::class,
                'queues' => ['other'],
            ],
            $config
        );
    }

    public function testIsLoaded(): void
    {
        $this->queueManager->use();

        $this->assertTrue(
            $this->queueManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->queueManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->queueManager->use('other');

        $this->assertTrue(
            $this->queueManager->isLoaded('other')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(QueueManager::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(Queue::class)
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->queueManager,
            $this->queueManager->setConfig('test', [
                'className' => TestQueue::class,
            ])
        );

        $config = $this->queueManager->getConfig('test');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => TestQueue::class,
            ],
            $config
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Queue config `default` already exists.');

        $this->queueManager->setConfig('default', [
            'className' => TestQueue::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->queueManager->use();

        $this->assertSame(
            $this->queueManager,
            $this->queueManager->unload()
        );

        $this->assertFalse(
            $this->queueManager->isLoaded()
        );
        $this->assertFalse(
            $this->queueManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->queueManager,
            $this->queueManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->queueManager->use('other');

        $this->assertSame(
            $this->queueManager,
            $this->queueManager->unload('other')
        );

        $this->assertFalse(
            $this->queueManager->isLoaded('other')
        );
        $this->assertFalse(
            $this->queueManager->hasConfig('other')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->queueManager->use();
        $handler2 = $this->queueManager->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            TestQueue::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(QueueManager::class);

        $container->use(Config::class)->set('Queue', [
            'default' => [
                'className' => TestQueue::class,
                'queues' => ['default'],
            ],
            'other' => [
                'className' => TestQueue::class,
                'queues' => ['other'],
            ],
        ]);

        $this->queueManager = $container->use(QueueManager::class);
    }
}
