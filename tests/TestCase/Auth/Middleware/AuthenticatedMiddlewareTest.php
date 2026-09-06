<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\UnauthorizedException;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticatedMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAuthenticated(): void
    {
        $auth = $this->createStub(Auth::class);
        $auth->method('isLoggedIn')->willReturn(true);

        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            new AuthenticatedMiddleware($auth)->process($this->request, $handler)
        );
    }

    public function testProcessUnauthenticatedHtml(): void
    {
        $auth = $this->createMock(Auth::class);
        $auth->method('isLoggedIn')->willReturn(false);
        $auth->expects($this->once())
            ->method('getLoginUrl')
            ->with($this->identicalTo($this->request->getUri()))
            ->willReturn('/login?redirect=%2Fprivate');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = new AuthenticatedMiddleware($auth)->process($this->request, $handler);

        $this->assertSame(
            '/login?redirect=%2Fprivate',
            $response->getHeaderLine('Location')
        );
    }

    public function testProcessUnauthenticatedJson(): void
    {
        $this->expectException(UnauthorizedException::class);

        $auth = $this->createStub(Auth::class);
        $auth->method('isLoggedIn')->willReturn(false);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $request = $this->request->withHeader('Accept', 'application/json');

        new AuthenticatedMiddleware($auth)->process($request, $handler);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class, [
            'options' => [
                'uri' => '/private',
            ],
        ]);
    }
}
