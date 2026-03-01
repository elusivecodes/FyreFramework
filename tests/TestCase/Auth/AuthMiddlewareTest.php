<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\Middleware\AuthenticatedMiddleware;
use Fyre\Auth\Middleware\AuthMiddleware;
use Fyre\Auth\Middleware\AuthorizedMiddleware;
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Authenticators\MockAuthenticator;
use Tests\Mock\Entities\User;

use function class_uses;

final class AuthMiddlewareTest extends TestCase
{
    use ConnectionTrait;

    public function testAuthenticatedMiddleware(): void
    {
        $this->login();

        $queue = new MiddlewareQueue([
            'authenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );
    }

    public function testAuthenticatedMiddlewareFail(): void
    {
        $queue = new MiddlewareQueue([
            'authenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            '/login',
            $response->getHeaderLine('Location')
        );
    }

    public function testAuthMiddleware(): void
    {
        $authenticator = $this->container->build(MockAuthenticator::class);
        $this->auth->addAuthenticator($authenticator);

        $queue = new MiddlewareQueue([
            'auth',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            'test',
            $response->getHeaderLine('Authenticated')
        );

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testAuthorizedMiddleware(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(User|null $authUser) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(User::class, $authUser);

            return true;
        });

        $queue = new MiddlewareQueue([
            'authorized:test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testAuthorizedMiddlewareArguments(): void
    {
        $this->login();

        $ran = false;
        $this->access->define('test', function(User|null $authUser, string $value) use (&$ran): bool {
            $ran = true;

            $this->assertInstanceOf(User::class, $authUser);
            $this->assertSame('test', $value);

            return true;
        });

        $queue = new MiddlewareQueue([
            'authorized:test,test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testAuthorizedMiddlewareFail(): void
    {
        $this->login();

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Forbidden');

        $this->access->define('test', function(User|null $authUser): bool {
            $this->assertInstanceOf(User::class, $authUser);

            return false;
        });

        $queue = new MiddlewareQueue([
            'authorized:test',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(AuthenticatedMiddleware::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(AuthMiddleware::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(AuthorizedMiddleware::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(UnauthenticatedMiddleware::class)
        );
    }

    public function testUnauthenticatedMiddleware(): void
    {
        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            '',
            $response->getHeaderLine('Location')
        );
    }

    public function testUnauthenticatedMiddlewareFail(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');

        $this->login();

        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testUnauthenticatedMiddlewareFailJson(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not Found');

        $this->login();

        $queue = new MiddlewareQueue([
            'unauthenticated',
        ]);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_ACCEPT' => 'application/json;q=0.9,text/plain',
                ],
            ],
        ]);

        $handler->handle($request);
    }
}
