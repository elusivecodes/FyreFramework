<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\MiddlewareRegistry;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Tests\Mock\Http\Middleware\ArgsMiddleware;
use Tests\Mock\Http\Middleware\MockMiddleware;

use function class_uses;

final class MiddlewareRegistryTest extends TestCase
{
    protected MiddlewareRegistry $middlewareRegistry;

    public function testClear(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid middleware: mock');

        $this->middlewareRegistry->map('mock', MockMiddleware::class);
        $this->middlewareRegistry->use('mock');

        $this->middlewareRegistry->clear();

        $this->middlewareRegistry->use('mock');
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(MiddlewareRegistry::class)
        );
    }

    public function testMapClassString(): void
    {
        $this->middlewareRegistry->map('mock', MockMiddleware::class);

        $this->assertInstanceOf(
            MockMiddleware::class,
            $this->middlewareRegistry->use('mock')
        );
    }

    public function testMapClassStringArguments(): void
    {
        $this->middlewareRegistry->map('mock', ArgsMiddleware::class, [
            'a' => 1,
            'b' => 2,
        ]);

        $middleware = $this->middlewareRegistry->use('mock');

        $this->assertInstanceOf(
            ArgsMiddleware::class,
            $middleware
        );

        $this->assertSame(
            [1, 2],
            $middleware->getArgs()
        );
    }

    public function testMapClosure(): void
    {
        $this->middlewareRegistry->map('mock', static fn(): MiddlewareInterface => new MockMiddleware());

        $this->assertInstanceOf(
            MockMiddleware::class,
            $this->middlewareRegistry->use('mock')
        );
    }

    public function testMapClosureArgs(): void
    {
        $this->middlewareRegistry->map('mock', static fn(int $a, int $b): MiddlewareInterface => new ArgsMiddleware($a, $b), [
            'a' => 1,
            'b' => 2,
        ]);

        $middleware = $this->middlewareRegistry->use('mock');

        $this->assertInstanceOf(
            ArgsMiddleware::class,
            $middleware
        );

        $this->assertSame(
            [1, 2],
            $middleware->getArgs()
        );
    }

    public function testUseClassString(): void
    {
        $this->assertInstanceOf(
            MockMiddleware::class,
            $this->middlewareRegistry->use(MockMiddleware::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();

        $this->middlewareRegistry = $container->build(MiddlewareRegistry::class);
    }
}
