<?php
declare(strict_types=1);

namespace Tests\TestCase\Event;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Event\MockListener;
use Tests\Mock\Event\MockPriorityListener;
use Tests\Mock\Event\MockProtectedListener;

use function class_uses;
use function mkdir;
use function rmdir;
use function unlink;

final class EventManagerTest extends TestCase
{
    protected Container $container;

    protected EventManager $eventManager;

    public function testAddListener(): void
    {
        $listener = new MockListener();

        $this->eventManager->addListener($listener);

        $this->eventManager->trigger('test', 1);

        $this->assertSame(1, $listener->getResult());
    }

    public function testAddListenerPriority(): void
    {
        $listener1 = new MockListener();
        $listener2 = new MockPriorityListener();

        $this->eventManager->addListener($listener1);
        $this->eventManager->addListener($listener2);

        $this->eventManager->trigger('test', 1);

        $this->assertNull($listener1->getResult());
        $this->assertSame(1, $listener2->getResult());
    }

    public function testAddListenerReturnsEventManager(): void
    {
        $this->assertSame(
            $this->eventManager,
            $this->eventManager->addListener(new MockListener())
        );
    }

    public function testAddProtectedListener(): void
    {
        $container = new Container();
        $container->singleton(CacheManager::class);
        $eventManager = $container->build(EventManager::class, [
            'parentEventManager' => null,
        ]);
        $listener = new MockProtectedListener();

        $eventManager->addListener($listener);

        $eventManager->trigger('test', 1);

        $this->assertSame(1, $listener->getResult());
    }

    public function testAddProtectedListenerReturnsEventManager(): void
    {
        $this->assertSame(
            $this->eventManager,
            $this->eventManager->addListener(new MockProtectedListener())
        );
    }

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

    public function testClear(): void
    {
        $this->eventManager->on('test', static function(): void {});

        $this->eventManager->clear();

        $this->assertFalse(
            $this->eventManager->has('test')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(EventManager::class)
        );
    }

    public function testDispatch(): void
    {
        $event = new Event('test');

        $this->eventManager->on('test', static function(): void {});

        $this->assertSame(
            $event,
            $this->eventManager->dispatch($event)
        );
    }

    public function testDispatchListeners(): void
    {
        $event = new Event('test');
        $events = [];

        $this->eventManager->on('test', static function(Event $event) use (&$events): void {
            $events[] = $event;
        });

        $this->eventManager->dispatch($event);

        $this->assertSame([$event], $events);
    }

    public function testEventDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Event::class)
        );
    }

    public function testEventPropagation(): void
    {
        $eventManager = $this->container->build(EventManager::class, [
            'parentEventManager' => $this->eventManager,
        ]);

        $results = [];

        $this->eventManager->on('test', static function() use (&$results): void {
            $results[] = 1;
        });
        $eventManager->on('test', static function() use (&$results): void {
            $results[] = 2;
        });

        $eventManager->trigger('test');

        $this->assertArraysAreIdentical([2, 1], $results);
    }

    public function testEventResult(): void
    {
        $this->eventManager->on('test', static function(Event $event): void {
            $event->setResult(1);
        });

        $event = $this->eventManager->trigger('test');

        $this->assertSame(
            1,
            $event->getResult()
        );
    }

    public function testEventStopPropagation(): void
    {
        $this->eventManager->on('test', static function(Event $event): void {
            $event->stopPropagation();
        });

        $event = $this->eventManager->trigger('test');

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testEventStopPropagationSkipsLocalListeners(): void
    {
        $events = [];

        $this->eventManager->on('test', static function(Event $event) use (&$events): void {
            $events[] = $event;
        });
        $this->eventManager->on('test', static function(Event $event): void {
            $event->stopPropagation();
        }, EventManager::PRIORITY_HIGH);

        $this->eventManager->trigger('test');

        $this->assertSame([], $events);
    }

    public function testEventStopPropagationSkipsParentListeners(): void
    {
        $eventManager = $this->container->build(EventManager::class, [
            'parentEventManager' => $this->eventManager,
        ]);

        $events = [];

        $this->eventManager->on('test', static function(Event $event) use (&$events): void {
            $events[] = $event;
        });
        $eventManager->on('test', static function(Event $event): void {
            $event->stopPropagation();
        }, EventManager::PRIORITY_HIGH);

        $eventManager->trigger('test');

        $this->assertSame([], $events);
    }

    public function testHas(): void
    {
        $this->eventManager->on('test', static function(): void {});

        $this->assertTrue(
            $this->eventManager->has('test')
        );
    }

    public function testHasInvalid(): void
    {
        $this->assertFalse(
            $this->eventManager->has('test')
        );
    }

    public function testOff(): void
    {
        $i = 0;

        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });
        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });

        $this->eventManager->off('test');

        $this->eventManager->trigger('test');

        $this->assertSame(0, $i);
    }

    public function testOffCallback(): void
    {
        $calls = [];
        $callback = static function() use (&$calls): void {
            $calls[] = 'removed';
        };

        $this->eventManager->on('test', $callback);
        $this->eventManager->on('test', static function() use (&$calls): void {
            $calls[] = 'remaining';
        });

        $this->eventManager->off('test', $callback);

        $this->eventManager->trigger('test');

        $this->assertSame(['remaining'], $calls);
    }

    public function testOffCallbackInvalid(): void
    {
        $i = 0;
        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });

        $this->eventManager->off('test', static function(): void {});

        $this->eventManager->trigger('test');

        $this->assertSame(1, $i);
    }

    public function testOffCallbackInvalidReturnsEventManager(): void
    {
        $this->eventManager->on('test', static function(): void {});

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test', static function(): void {})
        );
    }

    public function testOffCallbackReturnsEventManager(): void
    {
        $callback = static function(): void {};
        $this->eventManager->on('test', $callback);

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test', $callback)
        );
    }

    public function testOffInvalid(): void
    {
        $i = 0;
        $this->eventManager->on('test1', static function() use (&$i): void {
            $i++;
        });

        $this->eventManager->off('test2');

        $this->eventManager->trigger('test1');

        $this->assertSame(1, $i);
    }

    public function testOffInvalidReturnsEventManager(): void
    {
        $this->eventManager->on('test1', static function(): void {});

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test2')
        );
    }

    public function testOffReturnsEventManager(): void
    {
        $this->eventManager->on('test', static function(): void {});

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test')
        );
    }

    public function testOnReturnsEventManager(): void
    {
        $this->assertSame(
            $this->eventManager,
            $this->eventManager->on('test', static function(): void {})
        );
    }

    public function testRemoveListener(): void
    {
        $listener = new MockListener();

        $this->eventManager->addListener($listener);

        $this->eventManager->removeListener($listener);

        $this->eventManager->trigger('test', 1);

        $this->assertNull($listener->getResult());
    }

    public function testRemoveListenerReturnsEventManager(): void
    {
        $listener = new MockListener();
        $this->eventManager->addListener($listener);

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->removeListener($listener)
        );
    }

    public function testRemoveProtectedListener(): void
    {
        $container = new Container();
        $container->singleton(CacheManager::class);
        $eventManager = $container->build(EventManager::class, [
            'parentEventManager' => null,
        ]);
        $listener = new MockProtectedListener();

        $eventManager->addListener($listener);

        $eventManager->removeListener($listener);

        $eventManager->trigger('test', 1);

        $this->assertNull($listener->getResult());
    }

    public function testRemoveProtectedListenerReturnsEventManager(): void
    {
        $listener = new MockProtectedListener();
        $this->eventManager->addListener($listener);

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->removeListener($listener)
        );
    }

    public function testTriggerArguments(): void
    {
        $arguments = [];
        $this->eventManager->on('test', static function(Event $event, int $a, bool $b) use (&$arguments): void {
            $arguments = [$a, $b];
        });

        $this->eventManager->trigger('test', 2, true);

        $this->assertSame([2, true], $arguments);
    }

    public function testTriggerPriority(): void
    {
        $results = [];

        $this->eventManager->on('test', static function() use (&$results): void {
            $results[] = 1;
        });
        $this->eventManager->on('test', static function() use (&$results): void {
            $results[] = 2;
        }, EventManager::PRIORITY_HIGH);

        $this->eventManager->trigger('test');

        $this->assertArraysAreIdentical([2, 1], $results);
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
        @unlink('tmp/events.Tests.Mock.Event.MockPriorityListener');
        @unlink('tmp/events.Tests.Mock.Event.MockProtectedListener');
        @rmdir('tmp');
    }
}
