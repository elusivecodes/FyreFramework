<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\ControllerRoute;
use Tests\Mock\Controllers\HomeController;
use Tests\Mock\Controllers\TestController;

trait FindRouteTestTrait
{
    public function testGroupHost(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'test.com',
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

    public function testGroupHostInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'example.com',
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testGroupPort(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'test.com:8000',
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

    public function testGroupPortInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'example.com:80',
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testGroupScheme(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'test.com',
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

    public function testGroupSchemeInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testInvalidAction(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/test`.');

        $router = $this->container->use(Router::class);

        $router->get('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testInvalidRoute(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/test`.');

        $router = $this->container->use(Router::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testRouteHost(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'test.com',
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

    public function testRouteHostInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'example.com',
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testRouteOrder(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('{a}', HomeController::class);
        $router->get('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
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
    }

    public function testRoutePort(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'test.com:8000',
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

    public function testRoutePortInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'example.com:80',
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testRouteScheme(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'test.com',
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

    public function testRouteSchemeInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);
    }
}
