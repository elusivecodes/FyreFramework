<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Route;
use Fyre\Router\Routes\ClosureRoute;
use Override;
use PHPUnit\Framework\TestCase;

final class ClosureRouteTest extends TestCase
{
    protected Container $container;

    public function testGetDestination(): void
    {
        $callback = static function(): void {};

        $route = $this->container->build(ClosureRoute::class, ['destination' => $callback]);

        $this->assertInstanceOf(
            Route::class,
            $route
        );

        $this->assertSame(
            $callback,
            $route->getDestination()
        );
    }

    public function testSetArgumentsFromPath(): void
    {
        $route = $this->container->build(ClosureRoute::class, [
            'destination' => static function(): void {},
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
