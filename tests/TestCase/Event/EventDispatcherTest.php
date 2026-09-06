<?php
declare(strict_types=1);

namespace Tests\TestCase\Event;

use Fyre\Cache\CacheManager;
use Fyre\Core\Container;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Event\MockDispatcher;

final class EventDispatcherTest extends TestCase
{
    protected Container $container;

    protected MockDispatcher $dispatcher;

    public function testDispatchEvent(): void
    {
        $eventManager = $this->dispatcher->getEventManager();

        $events = [];
        $eventManager->on('test', static function(Event $event) use (&$events): void {
            $events[] = $event;
        });

        $event = $this->dispatcher->dispatchEvent('test', ['a' => 1]);

        $this->assertSame([$event], $events);
    }

    public function testDispatchEventData(): void
    {
        $event = $this->dispatcher->dispatchEvent('test', ['a' => 1]);

        $this->assertArraysAreIdentical(['a' => 1], $event->getData());
    }

    public function testDispatchEventName(): void
    {
        $event = $this->dispatcher->dispatchEvent('test');

        $this->assertSame('test', $event->getName());
    }

    public function testDispatchEventSubject(): void
    {
        $event = $this->dispatcher->dispatchEvent('test');

        $this->assertSame($this->dispatcher, $event->getSubject());
    }

    public function testGetEventManager(): void
    {
        $eventManager = $this->dispatcher->getEventManager();

        $this->assertSame(
            $eventManager,
            $this->dispatcher->getEventManager()
        );
    }

    public function testSetEventManager(): void
    {
        $eventManager = $this->container->build(EventManager::class, [
            'parentEventManager' => null,
        ]);

        $this->dispatcher->setEventManager($eventManager);

        $this->assertSame(
            $eventManager,
            $this->dispatcher->getEventManager()
        );
    }

    public function testSetEventManagerReturnsDispatcher(): void
    {
        $eventManager = $this->createStub(EventManager::class);

        $this->assertSame(
            $this->dispatcher,
            $this->dispatcher->setEventManager($eventManager)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(CacheManager::class);
        $this->container->singleton(EventManager::class);

        $this->dispatcher = $this->container->build(MockDispatcher::class);
    }
}
