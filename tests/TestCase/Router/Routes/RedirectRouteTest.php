<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Route;
use Fyre\Router\Routes\RedirectRoute;
use Override;
use PHPUnit\Framework\TestCase;

final class RedirectRouteTest extends TestCase
{
    protected Container $container;

    public function testGetDestination(): void
    {
        $route = $this->container->build(RedirectRoute::class, [
            'destination' => 'https://test.com/',
        ]);

        $this->assertSame(
            'https://test.com/',
            $route->getDestination()
        );
    }

    public function testRoute(): void
    {
        $route = $this->container->build(RedirectRoute::class, [
            'destination' => 'https://test.com/',
        ]);

        $this->assertInstanceOf(
            Route::class,
            $route
        );
    }

    public function testSetArgumentsFromPath(): void
    {
        $route = $this->container->build(RedirectRoute::class, [
            'destination' => 'https://test.com/{a}/{b}',
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
