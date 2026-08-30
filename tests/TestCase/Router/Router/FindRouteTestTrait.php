<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\Exceptions\MethodNotAllowedException;
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
                'uri' => 'http://test.com/home',
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
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'http://example.com/home',
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
                'uri' => 'http://test.com:8000/home',
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
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'http://example.com:80/home',
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
                'uri' => 'https://test.com/home',
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
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/home',
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testInvalidAction(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
                'uri' => '/test',
            ],
        ]);

        try {
            $router->parseRequest($request);
            $this->fail('A method-not-allowed exception was not thrown.');
        } catch (MethodNotAllowedException $exception) {
            $this->assertSame(
                'GET, HEAD',
                $exception->getHeaders()['Allow']
            );
        }
    }

    public function testInvalidActionMultipleMethods(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('test', TestController::class);
        $router->post('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'PUT',
                'uri' => '/test',
            ],
        ]);

        try {
            $router->parseRequest($request);
            $this->fail('A method-not-allowed exception was not thrown.');
        } catch (MethodNotAllowedException $exception) {
            $this->assertSame(
                'GET, POST, HEAD',
                $exception->getHeaders()['Allow']
            );
        }
    }

    public function testInvalidActionUnsupportedMethod(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'AIOHGOEWHGE',
                'uri' => '/test',
            ],
        ]);

        try {
            $router->parseRequest($request);
            $this->fail('A method-not-allowed exception was not thrown.');
        } catch (MethodNotAllowedException $exception) {
            $this->assertSame(
                'CONNECT, DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT, TRACE',
                $exception->getHeaders()['Allow']
            );
        }
    }

    public function testInvalidRoute(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('No route found for the path `/test`.');

        $router = $this->container->use(Router::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/test',
            ],
        ]);

        $router->parseRequest($request);
    }

    public function testRouteCustomMethod(): void
    {
        $router = $this->container->use(Router::class);
        $route = $router->connect('test', TestController::class, methods: ['PROPFIND']);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'PROPFIND',
                'uri' => '/test',
            ],
        ]);

        $this->assertSame(
            $route,
            $router->parseRequest($request)->getAttribute('route')
        );
    }

    public function testRouteHeadFallback(): void
    {
        $router = $this->container->use(Router::class);
        $route = $router->get('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'HEAD',
                'uri' => '/test',
            ],
        ]);

        $parsedRequest = $router->parseRequest($request);

        $this->assertSame(
            $route,
            $parsedRequest->getAttribute('route')
        );

        $this->assertSame(
            'HEAD',
            $parsedRequest->getMethod()
        );
    }

    public function testRouteHeadPrecedence(): void
    {
        $router = $this->container->use(Router::class);
        $router->get('test', TestController::class);
        $route = $router->connect('test', HomeController::class, methods: ['HEAD']);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'HEAD',
                'uri' => '/test',
            ],
        ]);

        $this->assertSame(
            $route,
            $router->parseRequest($request)->getAttribute('route')
        );
    }

    public function testRouteHost(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, host: 'TEST.COM');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'http://test.com/home',
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

        $this->assertSame(
            'test.com',
            $route->getHost()
        );
    }

    public function testRouteHostInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, host: 'test.com');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'http://example.com/home',
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
                'uri' => '/test',
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
                'uri' => 'http://test.com:8000/home',
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
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, port: 8000);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'http://example.com:80/home',
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
                'uri' => 'https://test.com/home',
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
        $this->expectExceptionMessageIs('No route found for the path `/home`.');

        $router = $this->container->use(Router::class);

        $router->get('home', HomeController::class, scheme: 'https');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/home',
            ],
        ]);

        $router->parseRequest($request);
    }
}
