<?php
declare(strict_types=1);

namespace Tests\TestCase\Event;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Container;
use Fyre\Event\EventManager;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Event\MockListener;
use Tests\Mock\Event\MockProtectedListener;

use function mkdir;
use function rmdir;
use function unlink;

final class EventManagerCacheTest extends TestCase
{
    protected Container $container;

    protected EventManager $eventManager;

    public function testCacheListener(): void
    {
        $listener = new MockListener();

        $this->eventManager->addListener($listener);

        $this->assertArraysAreIdentical(
            [
                [
                    'name' => 'test',
                    'priority' => 100,
                    'callback' => 'setResult',
                ],
            ],
            $this->container->use(CacheManager::class)
                ->use('_events')
                ->get('Tests.Mock.Event.MockListener')
        );
    }

    public function testCacheProtectedListener(): void
    {
        $listener = new MockProtectedListener();

        $this->eventManager->addListener($listener);

        $this->assertArraysAreIdentical(
            [
                [
                    'name' => 'test',
                    'priority' => 100,
                    'callback' => 'setResult',
                ],
            ],
            $this->container->use(CacheManager::class)
                ->use('_events')
                ->get('Tests.Mock.Event.MockProtectedListener')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(CacheManager::class);
        $this->container->use(CacheManager::class)->setConfig('_events', [
            'className' => FileCacher::class,
            'path' => 'tmp',
            'prefix' => 'events.',
            'expire' => 3600,
        ]);

        $this->eventManager = $this->container->build(EventManager::class, [
            'parentEventManager' => null,
        ]);

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/events.Tests.Mock.Event.MockListener');
        @unlink('tmp/events.Tests.Mock.Event.MockProtectedListener');
        @rmdir('tmp');
    }
}
