<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\ControllerRoute;
use Tests\Mock\Controllers\HomeController;

trait PrefixTestTrait
{
    public function testPrefix(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, prefix: 'prefix');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/prefix/home',
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

    public function testPrefixDeep(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->group(static function(Router $router): void {
                $router->get('home', HomeController::class);
            }, prefix: 'deep');
        }, prefix: 'prefix');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/prefix/deep/home',
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

    public function testPrefixEmptyRoute(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('', HomeController::class);
        }, prefix: 'prefix');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/prefix',
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

    public function testPrefixLeadingSlash(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, prefix: '/prefix');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/prefix/home',
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

    public function testPrefixTrailingSlash(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->get('home', HomeController::class);
        }, prefix: 'prefix/');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/prefix/home',
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
}
