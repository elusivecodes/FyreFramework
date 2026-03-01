<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\ClosureRoute;
use Fyre\Router\Routes\ControllerRoute;
use Tests\Mock\Controllers\HomeController;

trait PostTestTrait
{
    public function testPost(): void
    {
        $router = $this->container->use(Router::class);

        $router->post('home', HomeController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'post',
                'server' => [
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            ControllerRoute::class,
            $route
        );

        $this->assertSame(
            HomeController::class,
            $route->getController()
        );

        $this->assertSame(
            'index',
            $route->getAction()
        );
    }

    public function testPostAction(): void
    {
        $router = $this->container->use(Router::class);

        $router->post('home/alternate', [HomeController::class, 'altMethod']);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'post',
                'server' => [
                    'REQUEST_URI' => '/home/alternate',
                ],
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            ControllerRoute::class,
            $route
        );

        $this->assertSame(
            HomeController::class,
            $route->getController()
        );

        $this->assertSame(
            'altMethod',
            $route->getAction()
        );
    }

    public function testPostArguments(): void
    {
        $router = $this->container->use(Router::class);

        $router->post('home/alternate/{a}/{b}/{c}', [HomeController::class, 'altMethod']);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'post',
                'server' => [
                    'REQUEST_URI' => '/home/alternate/test/a/2',
                ],
            ],
        ]);

        $request = $router->parseRequest($request);
        $route = $request->getAttribute('route');

        $this->assertInstanceOf(
            ControllerRoute::class,
            $route
        );

        $this->assertSame(
            HomeController::class,
            $route->getController()
        );

        $this->assertSame(
            'altMethod',
            $route->getAction()
        );

        $this->assertSame(
            [
                'a' => 'test',
                'b' => 'a',
                'c' => '2',
            ],
            $request->getAttribute('routeArguments')
        );
    }

    public function testPostClosure(): void
    {
        $callback = static function(): void {};

        $router = $this->container->use(Router::class);

        $router->post('test', $callback);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'post',
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            ClosureRoute::class,
            $route
        );

        $this->assertSame(
            $callback,
            $route->getDestination()
        );
    }

    public function testPostClosureArguments(): void
    {
        $callback = static function(): void {};

        $router = $this->container->use(Router::class);

        $router->post('test/{a}/{b}', $callback);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'post',
                'server' => [
                    'REQUEST_URI' => '/test/a/2',
                ],
            ],
        ]);

        $request = $router->parseRequest($request);
        $route = $request->getAttribute('route');

        $this->assertInstanceOf(
            ClosureRoute::class,
            $route
        );

        $this->assertSame(
            $callback,
            $route->getDestination()
        );

        $this->assertSame(
            [
                'a' => 'a',
                'b' => '2',
            ],
            $request->getAttribute('routeArguments')
        );
    }
}
