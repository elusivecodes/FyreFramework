<?php
declare(strict_types=1);

namespace Tests\TestCase\Event;

use Fyre\Cache\CacheManager;
use Fyre\Core\Container;
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

        $ran = false;
        $eventManager->on('test', static function() use (&$ran): void {
            $ran = true;
        });

        $event = $this->dispatcher->dispatchEvent('test', ['a' => 1]);

        $this->assertSame('test', $event->getName());

        $this->assertSame($this->dispatcher, $event->getSubject());

        $this->assertSame(['a' => 1], $event->getData());

        $this->assertTrue($ran);
    }

    public function testGetEventManager(): void
    {
        $eventManager = $this->dispatcher->getEventManager();

        $this->assertInstanceOf(
            EventManager::class,
            $eventManager
        );

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

        $this->assertSame(
            $this->dispatcher,
            $this->dispatcher->setEventManager($eventManager)
        );

        $this->assertSame(
            $eventManager,
            $this->dispatcher->getEventManager()
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
