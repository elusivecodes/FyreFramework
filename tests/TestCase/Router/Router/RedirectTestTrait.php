<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\Router\Routes\RedirectRoute;

trait RedirectTestTrait
{
    public function testRedirect(): void
    {
        $router = $this->container->use(Router::class);

        $router->redirect('test', 'https://test.com/');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            RedirectRoute::class,
            $route
        );

        $this->assertSame(
            'https://test.com/',
            $route->getDestination()
        );
    }

    public function testRedirectArguments(): void
    {
        $router = $this->container->use(Router::class);

        $router->redirect('test/{a}/{b}', 'https://test.com/{a}/{b}');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/a/2',
                ],
            ],
        ]);

        $request = $router->parseRequest($request);

        $route = $request->getAttribute('route');

        $this->assertInstanceOf(
            RedirectRoute::class,
            $route
        );

        $response = $route->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            302,
            $response->getStatusCode()
        );

        $this->assertSame(
            'https://test.com/a/2',
            $response->getHeaderLine('Location')
        );
    }
}
