<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\ControllerRoute;
use Tests\Mock\Controllers\HomeController;

trait PlaceholderTestTrait
{
    public function testPlaceholders(): void
    {
        $router = $this->container->use(Router::class);

        $router->get('home/alternate/{a}/{b}/{c}', [HomeController::class, 'altMethod'], placeholders: [
            'a' => '[^/]+',
            'b' => '[a-z]+',
            'c' => '\d+',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
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
}
