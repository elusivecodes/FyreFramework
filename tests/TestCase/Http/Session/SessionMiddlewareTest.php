<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Middleware\SessionMiddleware;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class SessionMiddlewareTest extends TestCase
{
    protected Container $container;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(SessionMiddleware::class)
        );
    }

    public function testSessionMiddleware(): void
    {
        $middleware = $this->container->build(SessionMiddleware::class);

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
            $this->container->use(Session::class),
            $request->getAttribute('session')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(Session::class);
    }
}
