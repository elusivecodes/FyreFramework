<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Auth\Authenticator;
use Fyre\Auth\Middleware\AuthMiddleware;
use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\ORM\Entity;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAppliesAuthenticatorResponse(): void
    {
        $response = new ClientResponse();
        $authenticatedResponse = $response->withHeader('Authenticated', 'test');

        $authenticator = $this->createStub(Authenticator::class);
        $authenticator->method('beforeResponse')->willReturn($authenticatedResponse);

        $auth = $this->createStub(Auth::class);
        $auth->method('authenticators')->willReturn([$authenticator]);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $authenticatedResponse,
            new AuthMiddleware($auth)->process($this->request, $handler)
        );
    }

    public function testProcessAttachesAuth(): void
    {
        $auth = $this->createStub(Auth::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(static fn(ServerRequestInterface $request): bool => $request->getAttribute('auth') === $auth))
            ->willReturn(new ClientResponse());

        new AuthMiddleware($auth)->process($this->request, $handler);
    }

    public function testProcessAttachesUser(): void
    {
        $user = new Entity([], 'Users');

        $auth = $this->createStub(Auth::class);
        $auth->method('authenticate')->willReturn($user);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(static fn(ServerRequestInterface $request): bool => $request->getAttribute('user') === $user))
            ->willReturn(new ClientResponse());

        new AuthMiddleware($auth)->process($this->request, $handler);
    }

    public function testProcessAuthenticatesWithAuthAttribute(): void
    {
        $auth = $this->createMock(Auth::class);
        $auth->expects($this->once())
            ->method('authenticate')
            ->with($this->callback(static fn(ServerRequestInterface $request): bool => $request->getAttribute('auth') === $auth))
            ->willReturn(null);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new ClientResponse());

        new AuthMiddleware($auth)->process($this->request, $handler);
    }

    public function testProcessPassesCurrentUserToAuthenticator(): void
    {
        $response = new ClientResponse();
        $originalUser = new Entity([], 'Users');
        $currentUser = new Entity([], 'Users');

        $authenticator = $this->createMock(Authenticator::class);
        $authenticator->expects($this->once())
            ->method('beforeResponse')
            ->with($this->identicalTo($response), $this->identicalTo($currentUser))
            ->willReturn($response);

        $auth = $this->createStub(Auth::class);
        $auth->method('authenticators')->willReturn([$authenticator]);
        $auth->method('authenticate')->willReturn($originalUser);
        $auth->method('user')->willReturn($currentUser);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        new AuthMiddleware($auth)->process($this->request, $handler);
    }

    public function testProcessReturnsHandlerResponse(): void
    {
        $auth = $this->createStub(Auth::class);

        $response = new ClientResponse();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $response,
            new AuthMiddleware($auth)->process($this->request, $handler)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class);
    }
}
