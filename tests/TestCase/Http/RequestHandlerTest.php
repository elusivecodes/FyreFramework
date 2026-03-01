<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Tests\Mock\Http\Middleware\ArgsMiddleware;
use Tests\Mock\Http\Middleware\MockMiddleware;

final class RequestHandlerTest extends TestCase
{
    protected Container $container;

    protected MiddlewareRegistry $middlewareRegistry;

    public function testDefaultResponse(): void
    {
        $queue = new MiddlewareQueue();

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    public function testRun(): void
    {
        $middleware1 = new MockMiddleware();
        $middleware2 = new MockMiddleware();

        $queue = new MiddlewareQueue([
            $middleware1,
            $middleware2,
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue(
            $middleware1->isLoaded()
        );

        $this->assertTrue(
            $middleware2->isLoaded()
        );

        $this->assertSame(
            $request,
            $this->container->use(ServerRequest::class)
        );
    }

    public function testRunGroup(): void
    {
        $middleware1 = new MockMiddleware();
        $middleware2 = new MockMiddleware();

        $this->middlewareRegistry->group('test', [
            $middleware1,
            $middleware2,
        ]);

        $queue = new MiddlewareQueue(['test']);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue(
            $middleware1->isLoaded()
        );

        $this->assertTrue(
            $middleware2->isLoaded()
        );

        $this->assertSame(
            $request,
            $this->container->use(ServerRequest::class)
        );
    }

    public function testRunMapClosureWithArgs()
    {
        $this->middlewareRegistry->map('mock', static fn(): MiddlewareInterface => new ArgsMiddleware());

        $queue = new MiddlewareQueue([
            'mock:1,2,3',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            '[
    "1",
    "2",
    "3"
]',
            $response->getBody()->getContents()
        );
    }

    public function testRunMapWithArgs()
    {
        $this->middlewareRegistry->map('mock', ArgsMiddleware::class);

        $queue = new MiddlewareQueue([
            'mock:1,2,3',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            '[
    "1",
    "2",
    "3"
]',
            $response->getBody()->getContents()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(MiddlewareRegistry::class);

        $this->middlewareRegistry = $this->container->use(MiddlewareRegistry::class);
    }
}
