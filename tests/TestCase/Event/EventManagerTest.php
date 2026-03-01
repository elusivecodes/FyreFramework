<?php
declare(strict_types=1);

namespace Tests\TestCase\Event;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Event\MockListener;
use Tests\Mock\Event\MockPriorityListener;

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

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->addListener($listener)
        );

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

    public function testCacheListener(): void
    {
        $listener = new MockListener();

        $this->eventManager->addListener($listener);

        $this->assertSame(
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

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(EventManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Event::class)
        );
    }

    public function testDispatch(): void
    {
        $event1 = new Event('test');

        $i = 0;

        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });

        $event2 = $this->eventManager->dispatch($event1);

        $this->assertInstanceOf(Event::class, $event2);

        $this->assertSame($event1, $event2);

        $this->assertSame(1, $i);
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

        $event = $eventManager->trigger('test');

        $this->assertSame([2, 1], $results);
    }

    public function testEventResult(): void
    {
        $this->eventManager->on('test', static function(Event $event): void {
            $event->setResult(1);
        });

        $event = $this->eventManager->trigger('test');

        $this->assertInstanceOf(Event::class, $event);

        $this->assertSame(
            1,
            $event->getResult()
        );
    }

    public function testEventStopPropagation(): void
    {
        $eventManager = $this->container->build(EventManager::class, [
            'parentEventManager' => $this->eventManager,
        ]);

        $ran = false;

        $this->eventManager->on('test', static function() use (&$ran): void {
            $ran = true;
        });
        $eventManager->on('test', static function() use (&$ran): void {
            $ran = true;
        });
        $eventManager->on('test', static function(Event $event): void {
            $event->stopPropagation();
        }, EventManager::PRIORITY_HIGH);

        $event = $eventManager->trigger('test');

        $this->assertTrue(
            $event->isPropagationStopped()
        );

        $this->assertFalse($ran);
    }

    public function testHas(): void
    {
        $this->assertSame(
            $this->eventManager,
            $this->eventManager->on('test', static function(): void {})
        );

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

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test')
        );

        $this->eventManager->trigger('test');

        $this->assertSame(0, $i);
    }

    public function testOffCallback(): void
    {
        $i = 0;
        $callback = static function() use (&$i): void {
            $i++;
        };

        $this->eventManager->on('test', $callback);
        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test', $callback)
        );

        $this->eventManager->trigger('test');

        $this->assertSame(1, $i);
    }

    public function testOffCallbackInvalid(): void
    {
        $i = 0;
        $this->eventManager->on('test', static function() use (&$i): void {
            $i++;
        });

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test', static function() use (&$i): void {})
        );

        $this->eventManager->trigger('test');

        $this->assertSame(1, $i);
    }

    public function testOffInvalid(): void
    {
        $i = 0;
        $this->eventManager->on('test1', static function() use (&$i): void {
            $i++;
        });

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->off('test2')
        );

        $this->eventManager->trigger('test1');

        $this->assertSame(1, $i);
    }

    public function testRemoveListener(): void
    {
        $listener = new MockListener();

        $this->eventManager->addListener($listener);

        $this->assertSame(
            $this->eventManager,
            $this->eventManager->removeListener($listener)
        );

        $this->eventManager->trigger('test', 1);

        $this->assertNull($listener->getResult());
    }

    public function testTriggerArguments(): void
    {
        $i = 0;
        $this->eventManager->on('test', static function(Event $event, int $a, bool $b) use (&$i): void {
            if ($b) {
                $i += $a;
            }
        });

        $this->eventManager->trigger('test', 2, true);

        $this->assertSame(2, $i);
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

        $this->assertSame([2, 1], $results);
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
        @rmdir('tmp');
    }
}
