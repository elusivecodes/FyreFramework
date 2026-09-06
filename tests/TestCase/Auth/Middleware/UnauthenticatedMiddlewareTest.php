<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class UnauthenticatedMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    /**
     * @return array<string, array{string}>
     */
    public static function acceptProvider(): array
    {
        return [
            'html' => ['text/html'],
            'json' => ['application/json'],
        ];
    }

    #[DataProvider('acceptProvider')]
    public function testProcessAuthenticated(string $accept): void
    {
        $this->expectException(NotFoundException::class);

        $auth = $this->createStub(Auth::class);
        $auth->method('isLoggedIn')->willReturn(true);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $request = $this->request->withHeader('Accept', $accept);

        new UnauthenticatedMiddleware($auth)->process($request, $handler);
    }

    public function testProcessUnauthenticated(): void
    {
        $auth = $this->createStub(Auth::class);
        $auth->method('isLoggedIn')->willReturn(false);

        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            new UnauthenticatedMiddleware($auth)->process($this->request, $handler)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class);
    }
}
