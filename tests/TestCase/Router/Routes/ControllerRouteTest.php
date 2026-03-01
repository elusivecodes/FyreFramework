<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Route;
use Fyre\Router\Routes\ControllerRoute;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\TestController;

final class ControllerRouteTest extends TestCase
{
    protected Container $container;

    public function testGetAction(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            'test',
            $route->getAction()
        );
    }

    public function testGetController(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            TestController::class,
            $route->getController()
        );
    }

    public function testGetDestination(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            [TestController::class, 'test'],
            $route->getDestination()
        );
    }

    public function testRoute(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertInstanceOf(
            Route::class,
            $route
        );
    }

    public function testSetArgumentsFromPath(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}/{b}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/a/1',
                ],
            ],
        ]);

        $request = $route->parseRequest($request);

        $this->assertInstanceOf(
            ServerRequest::class,
            $request
        );

        $this->assertSame(
            [
                'a' => 'a',
                'b' => '1',
            ],
            $request->getAttribute('routeArguments')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
