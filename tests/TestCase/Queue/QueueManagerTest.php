<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function getenv;

final class QueueManagerTest extends TestCase
{
    protected QueueManager $queueManager;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            RedisQueue::class,
            $this->queueManager->build([
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Queue `Invalid` must extend `Fyre\Queue\Queue`.');

        $this->queueManager->build([
            'className' => 'Invalid',
        ]);
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

    public function testDebugRedisQueue(): void
    {
        $data = $this->queueManager->use()
            ->__debugInfo();

        $this->assertSame(
            [
                '[class]' => RedisQueue::class,
                'config' => [
                    'host' => '[*****]',
                    'password' => '',
                    'port' => '[*****]',
                    'database' => '',
                    'timeout' => 0,
                    'persist' => true,
                    'tls' => false,
                    'ssl' => [
                        'key' => null,
                        'cert' => null,
                        'ca' => null,
                    ],
                    'className' => RedisQueue::class,
                ],
                'connection' => '[Redis]',
                'container' => '[Fyre\Core\Container]',
            ],
            $data
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => RedisQueue::class,
                    'host' => getenv('REDIS_HOST'),
                    'password' => getenv('REDIS_PASSWORD'),
                    'database' => getenv('REDIS_DATABASE'),
                    'port' => getenv('REDIS_PORT'),
                ],
                'other' => [
                    'className' => RedisQueue::class,
                    'host' => getenv('REDIS_HOST'),
                    'password' => getenv('REDIS_PASSWORD'),
                    'database' => getenv('REDIS_DATABASE'),
                    'port' => getenv('REDIS_PORT'),
                ],
            ],
            $this->queueManager->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
            $this->queueManager->getConfig('other')
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
                'className' => RedisQueue::class,
            ])
        );

        $this->assertSame(
            [
                'className' => RedisQueue::class,
            ],
            $this->queueManager->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Queue config `default` already exists.');

        $this->queueManager->setConfig('default', [
            'className' => RedisQueue::class,
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
            RedisQueue::class,
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
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
            'other' => [
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
        ]);

        $this->queueManager = $container->use(QueueManager::class);
    }
}
