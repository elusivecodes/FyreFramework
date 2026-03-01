<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Routes\ControllerRoute;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\TestController;

final class RouteTest extends TestCase
{
    protected Container $container;

    public function testCheckMethod(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'methods' => ['GET'],
        ]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckMethodInvalid(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'methods' => ['GET'],
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
            ],
        ]);

        $this->assertNull(
            $route->parseRequest($request)
        );
    }

    public function testCheckMethodNoMethods(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckPath(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/a',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckPathInvalid(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/invalid',
                ],
            ],
        ]);

        $this->assertNull(
            $route->parseRequest($request)
        );
    }

    public function testGetPath(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $this->assertSame(
            'test/{a}',
            $route->getPath()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
