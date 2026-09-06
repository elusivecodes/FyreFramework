<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth\Middleware;

use Fyre\Auth\Access;
use Fyre\Auth\Auth;
use Fyre\Auth\Middleware\AuthorizedMiddleware;
use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\Http\ServerRequest;
use Fyre\ORM\Entity;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthorizedMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAllowed(): void
    {
        $access = $this->createStub(Access::class);
        $access->method('allows')->willReturn(true);

        $auth = $this->createStub(Auth::class);
        $auth->method('access')->willReturn($access);

        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            new AuthorizedMiddleware($auth)->process($this->request, $handler, 'edit')
        );
    }

    public function testProcessDeniedAuthenticated(): void
    {
        $this->expectException(ForbiddenException::class);

        $access = $this->createStub(Access::class);
        $access->method('allows')->willReturn(false);

        $auth = $this->createStub(Auth::class);
        $auth->method('access')->willReturn($access);
        $auth->method('isLoggedIn')->willReturn(true);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        new AuthorizedMiddleware($auth)->process($this->request, $handler, 'edit');
    }

    public function testProcessDeniedGuestHtml(): void
    {
        $access = $this->createStub(Access::class);
        $access->method('allows')->willReturn(false);

        $auth = $this->createMock(Auth::class);
        $auth->method('access')->willReturn($access);
        $auth->method('isLoggedIn')->willReturn(false);
        $auth->expects($this->once())
            ->method('getLoginUrl')
            ->with($this->identicalTo($this->request->getUri()))
            ->willReturn('/login?redirect=%2Fprivate');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $response = new AuthorizedMiddleware($auth)->process($this->request, $handler, 'edit');

        $this->assertSame(
            '/login?redirect=%2Fprivate',
            $response->getHeaderLine('Location')
        );
    }

    public function testProcessDeniedGuestJson(): void
    {
        $this->expectException(ForbiddenException::class);

        $access = $this->createStub(Access::class);
        $access->method('allows')->willReturn(false);

        $auth = $this->createStub(Auth::class);
        $auth->method('access')->willReturn($access);
        $auth->method('isLoggedIn')->willReturn(false);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $request = $this->request->withHeader('Accept', 'application/json');

        new AuthorizedMiddleware($auth)->process($request, $handler, 'edit');
    }

    public function testProcessLiteralArguments(): void
    {
        $access = $this->createMock(Access::class);
        $access->expects($this->once())
            ->method('allows')
            ->with('edit', 'value')
            ->willReturn(true);

        $auth = $this->createStub(Auth::class);
        $auth->method('access')->willReturn($access);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new AuthorizedMiddleware($auth)->process($this->request, $handler, 'edit', 'value');
    }

    public function testProcessRouteArguments(): void
    {
        $entity = new Entity([], 'Posts');
        $request = $this->request->withAttribute('routeArguments', ['post' => $entity]);

        $access = $this->createMock(Access::class);
        $access->expects($this->once())
            ->method('allows')
            ->with('edit', $this->identicalTo($entity))
            ->willReturn(true);

        $auth = $this->createStub(Auth::class);
        $auth->method('access')->willReturn($access);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new AuthorizedMiddleware($auth)->process($request, $handler, 'edit', 'post');
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
