<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Security\CsrfProtection;
use Fyre\Security\Exceptions\CsrfTokenException;
use Fyre\Security\Middleware\CsrfProtectionMiddleware;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function class_uses;

final class CsrfProtectionMiddlewareTest extends TestCase
{
    protected Container $container;

    public function testCookieInvalid(): void
    {
        $this->expectException(CsrfTokenException::class);
        $this->expectExceptionMessage('CSRF Token Mismatch');

        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'headers' => [
                    'Csrf-Token' => $csrfProtection->getFormToken(),
                ],
                'cookies' => [
                    'CsrfToken' => $csrfProtection->getCookieToken().'1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testCookieMissing(): void
    {
        $this->expectException(CsrfTokenException::class);
        $this->expectExceptionMessage('CSRF Token Mismatch');

        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'headers' => [
                    'Csrf-Token' => $csrfProtection->getFormToken(),
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CsrfProtectionMiddleware::class)
        );
    }

    public function testFormTokenHeader(): void
    {
        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'headers' => [
                    'Csrf-Token' => $csrfProtection->getFormToken(),
                ],
                'cookies' => [
                    'CsrfToken' => $csrfProtection->getCookieToken(),
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testFormTokenInvalid(): void
    {
        $this->expectException(CsrfTokenException::class);
        $this->expectExceptionMessage('CSRF Token Mismatch');

        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'cookies' => [
                    'CsrfToken' => $csrfProtection->getCookieToken().'1',
                ],
                'data' => [
                    'csrf_token' => $csrfProtection->getFormToken(),
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testFormTokenMissing(): void
    {
        $this->expectException(CsrfTokenException::class);
        $this->expectExceptionMessage('CSRF Token Mismatch');

        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'cookies' => [
                    'CsrfToken' => $csrfProtection->getCookieToken().'1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testFormTokenPost(): void
    {
        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'cookies' => [
                    'CsrfToken' => $csrfProtection->getCookieToken(),
                ],
                'data' => [
                    'csrf_token' => $csrfProtection->getFormToken(),
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $request = $this->container->use(ServerRequest::class);

        $this->assertNull(
            $request->getData('csrf_token')
        );
    }

    public function testGet(): void
    {
        $csrfProtection = $this->container->use(CsrfProtection::class);
        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $request = $this->container->use(ServerRequest::class);

        $this->assertSame(
            $csrfProtection,
            $request->getAttribute('csrf')
        );

        [$cookieString] = $response->getHeader('Set-Cookie');

        $cookie = Cookie::createFromHeaderString($cookieString);

        $this->assertSame(
            'CsrfToken',
            $cookie->getName()
        );

        $this->assertSame(
            $csrfProtection->getCookieToken(),
            $cookie->getValue()
        );
    }

    public function testSkipCheck(): void
    {
        $this->container->use(Config::class)->set('Csrf.skipCheck', function(ServerRequestInterface $request): bool {
            $this->assertInstanceOf(
                ServerRequest::class,
                $request
            );

            return true;
        });

        $middleware = $this->container->build(CsrfProtectionMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(CsrfProtection::class);

        $this->container->use(Config::class)->set('Csrf.salt', 'l2wyQow3eTwQeTWcfZnlgU8FnbiWljpGjQvNP2pL');
    }
}
