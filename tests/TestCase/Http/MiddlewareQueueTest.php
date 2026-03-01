<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Tests\Mock\Http\Middleware\MockMiddleware;

use function class_uses;

final class MiddlewareQueueTest extends TestCase
{
    protected MiddlewareRegistry $middlewareRegistry;

    protected MiddlewareQueue $queue;

    public function testAdd(): void
    {
        $this->queue->add(new MockMiddleware());

        $this->assertSame(
            5,
            $this->queue->count()
        );
    }

    public function testCount(): void
    {
        $this->assertSame(
            4,
            $this->queue->count()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(MiddlewareQueue::class)
        );
    }

    public function testInsertAt(): void
    {
        $middleware = new MockMiddleware();

        $this->queue->insertAt(1, $middleware);
        $this->queue->next();

        $this->assertSame(
            $middleware,
            $this->queue->current()
        );
    }

    public function testIteration(): void
    {
        foreach ($this->queue as $middleware) {
            $this->assertInstanceOf(
                MiddlewareInterface::class,
                $this->middlewareRegistry->resolve($middleware)
            );
        }
    }

    public function testPrepend(): void
    {
        $middleware = new MockMiddleware();

        $this->queue->prepend($middleware);

        $this->assertSame(
            $middleware,
            $this->queue->current()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();

        $this->middlewareRegistry = $container->build(MiddlewareRegistry::class);
        $this->middlewareRegistry->map('mock', MockMiddleware::class);
        $this->middlewareRegistry->map('mock-closure', static fn(): MiddlewareInterface => new MockMiddleware());

        $this->queue = new MiddlewareQueue([
            new MockMiddleware(),
            MockMiddleware::class,
            'mock',
            'mock-closure',
        ]);
    }
}
