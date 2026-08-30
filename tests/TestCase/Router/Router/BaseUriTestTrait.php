<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Core\Config;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\ControllerRoute;
use Tests\Mock\Controllers\TestController;

trait BaseUriTestTrait
{
    public function testRouteBaseUri(): void
    {
        $this->container->use(Config::class)->set('App.baseUri', 'https://test.com/deep/');

        $router = $this->container->build(Router::class);
        $router->get('test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'https://test.com/deep/test',
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            ControllerRoute::class,
            $route
        );

        $this->assertSame(
            TestController::class,
            $route->getController()
        );
    }

    public function testRouteBaseUriExactPath(): void
    {
        $this->container->use(Config::class)->set('App.baseUri', 'https://test.com/deep/');

        $router = $this->container->build(Router::class);
        $router->get('', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'https://test.com/deep',
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(ControllerRoute::class, $route);
    }

    public function testRouteBaseUriRequiresSegmentBoundary(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('No route found for the path `/deeper/test`.');

        $this->container->use(Config::class)->set('App.baseUri', 'https://test.com/deep/');

        $router = $this->container->build(Router::class);
        $router->get('er/test', TestController::class);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => 'https://test.com/deeper/test',
            ],
        ]);

        $router->parseRequest($request);
    }
}
