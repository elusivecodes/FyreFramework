<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Middleware;

use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Middleware\SessionMiddleware;
use Fyre\Http\ServerRequest;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final class SessionMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    /**
     * @return array<string, array{string}>
     */
    public static function safeMethodProvider(): array
    {
        return [
            'get' => ['GET'],
            'head' => ['HEAD'],
            'options' => ['OPTIONS'],
            'trace' => ['TRACE'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeMethodProvider(): array
    {
        return [
            'post' => ['POST'],
            'put' => ['PUT'],
            'patch' => ['PATCH'],
            'delete' => ['DELETE'],
            'connect' => ['CONNECT'],
        ];
    }

    public function testProcessClosesAfterHandler(): void
    {
        $calls = [];
        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('close')
            ->willReturnCallback(static function() use (&$calls): bool {
                $calls[] = 'close';

                return true;
            });

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(static function() use (&$calls): ClientResponse {
            $calls[] = 'handler';

            return new ClientResponse();
        });

        new SessionMiddleware($session)->process($this->request, $handler);

        $this->assertSame(['handler', 'close'], $calls);
    }

    public function testProcessClosesAfterHandlerException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Handler failed');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('close')->willReturn(true);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willThrowException(new RuntimeException('Handler failed'));

        new SessionMiddleware($session)->process($this->request, $handler);
    }

    public function testProcessForwardsSessionAttribute(): void
    {
        $session = $this->createStub(Session::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(static fn(ServerRequestInterface $request): bool => $request->getAttribute('session') === $session))
            ->willReturn(new ClientResponse());

        new SessionMiddleware($session)->process($this->request, $handler);
    }

    public function testProcessReturnsHandlerResponse(): void
    {
        $session = $this->createStub(Session::class);
        $response = new ClientResponse();

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $response,
            new SessionMiddleware($session)->process($this->request, $handler)
        );
    }

    #[DataProvider('safeMethodProvider')]
    public function testProcessStartsReadOnlySession(string $method): void
    {
        $session = $this->createMock(Session::class);
        $session->method('allowReadOnly')->willReturn(true);
        $session->expects($this->once())->method('startReadOnly');
        $session->expects($this->never())->method('start');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new SessionMiddleware($session)->process($this->request->withMethod($method), $handler);
    }

    #[DataProvider('safeMethodProvider')]
    public function testProcessStartsSessionWhenReadOnlyDisabled(string $method): void
    {
        $session = $this->createMock(Session::class);
        $session->method('allowReadOnly')->willReturn(false);
        $session->expects($this->once())->method('start');
        $session->expects($this->never())->method('startReadOnly');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new SessionMiddleware($session)->process($this->request->withMethod($method), $handler);
    }

    #[DataProvider('unsafeMethodProvider')]
    public function testProcessStartsSessionWithUnsafeMethod(string $method): void
    {
        $session = $this->createMock(Session::class);
        $session->method('allowReadOnly')->willReturn(true);
        $session->expects($this->once())->method('start');
        $session->expects($this->never())->method('startReadOnly');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new SessionMiddleware($session)->process($this->request->withMethod($method), $handler);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class);
    }
}
