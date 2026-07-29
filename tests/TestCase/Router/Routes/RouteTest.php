<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Routes\ControllerRoute;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\TestController;

final class RouteTest extends TestCase
{
    protected Container $container;

    public function testCheckMethod(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'methods' => ['GET'],
        ]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckMethodInvalid(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'methods' => ['GET'],
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
            ],
        ]);

        $this->assertNull(
            $route->parseRequest($request)
        );
    }

    public function testCheckMethodNoMethods(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $request = $this->container->build(ServerRequest::class);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckPath(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/a',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($request)
        );
    }

    public function testCheckPathInvalid(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/invalid',
                ],
            ],
        ]);

        $this->assertNull(
            $route->parseRequest($request)
        );
    }

    public function testCheckPathInvalidLineFeed(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $request = $this->container
            ->build(ServerRequest::class)
            ->withAttribute('relativePath', "test/a\n");

        $this->assertNull(
            $route->parseRequest($request)
        );
    }

    public function testCheckPathPlaceholderBindingConstraintAndOptionalMarker(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{item:id?}',
            'placeholders' => [
                'item' => '\\d+',
            ],
        ]);

        $this->assertSame(
            ['item' => 'id'],
            $route->getBindingFields()
        );

        $matchingRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/123',
                ],
            ],
        ]);

        $optionalRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $invalidRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/value',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($matchingRequest)
        );

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->parseRequest($optionalRequest)
        );

        $this->assertNull(
            $route->parseRequest($invalidRequest)
        );
    }

    public function testGetPath(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}',
        ]);

        $this->assertSame(
            'test/{a}',
            $route->getPath()
        );
    }

    public function testSetHost(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setHost('TEST.COM')
        );

        $this->assertSame(
            'test.com',
            $route->getHost()
        );
    }

    public function testSetPort(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setPort(8000)
        );

        $this->assertSame(
            8000,
            $route->getPort()
        );
    }

    public function testSetPortInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route port must be between 1 and 65535.');

        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $route->setPort(0);
    }

    public function testSetPortInvalidMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route port must be between 1 and 65535.');

        $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'port' => 65536,
        ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
