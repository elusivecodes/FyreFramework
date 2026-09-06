<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Tests\Mock\Http\Middleware\ArgsMiddleware;

use function class_uses;

final class RequestHandlerTest extends TestCase
{
    protected Container $container;

    protected MiddlewareRegistry $middlewareRegistry;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(RequestHandler::class)
        );
    }

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

    public function testFallbackException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Fallback failed');

        $fallbackHandler = $this->createStub(RequestHandlerInterface::class);
        $fallbackHandler->method('handle')->willThrowException(new RuntimeException('Fallback failed'));

        $handler = $this->container->build(RequestHandler::class, [
            'queue' => new MiddlewareQueue(),
            'fallbackHandler' => $fallbackHandler,
        ]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testFallbackResponse(): void
    {
        $request = $this->container->build(ServerRequest::class);
        $response = new ClientResponse();

        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);
        $fallbackHandler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $handler = $this->container->build(RequestHandler::class, [
            'queue' => new MiddlewareQueue(),
            'fallbackHandler' => $fallbackHandler,
        ]);

        $this->assertSame(
            $response,
            $handler->handle($request)
        );
    }

    public function testRun(): void
    {
        $request = $this->container->build(ServerRequest::class);

        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($request), $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request));

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($request), $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request));

        $queue = new MiddlewareQueue([
            $middleware1,
            $middleware2,
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $handler->handle($request);
    }

    public function testRunException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Middleware failed');

        $middleware = $this->createStub(MiddlewareInterface::class);
        $middleware->method('process')->willThrowException(new RuntimeException('Middleware failed'));

        $queue = new MiddlewareQueue([$middleware]);
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testRunGroup(): void
    {
        $request = $this->container->build(ServerRequest::class);

        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($request), $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request));

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($request), $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request));

        $this->middlewareRegistry->group('test', [
            $middleware1,
            $middleware2,
        ]);

        $queue = new MiddlewareQueue(['test']);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $handler->handle($request);
    }

    public function testRunGroupOrder(): void
    {
        $calls = [];

        $this->middlewareRegistry->group('test', [
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$calls): ResponseInterface {
                $calls[] = 'first before';
                $response = $handler->handle($request);
                $calls[] = 'first after';

                return $response;
            },
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$calls): ResponseInterface {
                $calls[] = 'second before';
                $response = $handler->handle($request);
                $calls[] = 'second after';

                return $response;
            },
        ]);

        $queue = new MiddlewareQueue([
            'test',
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$calls): ResponseInterface {
                $calls[] = 'next';

                return $handler->handle($request);
            },
        ]);
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);

        $this->assertSame(
            ['first before', 'second before', 'next', 'second after', 'first after'],
            $calls
        );
    }

    public function testRunMapClosureWithArgs(): void
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

    public function testRunMapWithArgs(): void
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

    public function testRunOrder(): void
    {
        $calls = [];

        $queue = new MiddlewareQueue([
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$calls): ResponseInterface {
                $calls[] = 'first before';
                $response = $handler->handle($request);
                $calls[] = 'first after';

                return $response;
            },
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$calls): ResponseInterface {
                $calls[] = 'second before';
                $response = $handler->handle($request);
                $calls[] = 'second after';

                return $response;
            },
        ]);
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);

        $this->assertSame(
            ['first before', 'second before', 'second after', 'first after'],
            $calls
        );
    }

    public function testRunRegistersRequest(): void
    {
        $queue = new MiddlewareQueue();
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);

        $this->assertSame(
            $request,
            $this->container->use(ServerRequest::class)
        );
    }

    public function testRunScopedRequest(): void
    {
        $this->container->scoped(ServerRequest::class);

        $queue = new MiddlewareQueue();
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);

        $this->assertSame(
            $request,
            $this->container->use(ServerRequest::class)
        );
    }

    public function testRunScopedRequestCleared(): void
    {
        $this->container->scoped(ServerRequest::class);

        $queue = new MiddlewareQueue();
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);

        $this->container->clearScoped();

        $this->assertNotSame(
            $request,
            $this->container->use(ServerRequest::class)
        );
    }

    public function testRunShortCircuit(): void
    {
        $response = new ClientResponse();
        $middleware1 = $this->createStub(MiddlewareInterface::class);
        $middleware1->method('process')->willReturn($response);

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->expects($this->never())->method('process');

        $fallbackHandler = $this->createMock(RequestHandlerInterface::class);
        $fallbackHandler->expects($this->never())->method('handle');

        $queue = new MiddlewareQueue([$middleware1, $middleware2]);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $fallbackHandler,
        ]);
        $request = $this->container->build(ServerRequest::class);

        $this->assertSame(
            $response,
            $handler->handle($request)
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
