<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Routes;

use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Routes\ControllerRoute;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\TestController;

final class RouteTest extends TestCase
{
    protected Container $container;

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function pathArgumentProvider(): array
    {
        return [
            'plain' => ['/test/value', 'value'],
            'space' => ['/test/review%20pending', 'review pending'],
            'unicode' => ['/test/caf%C3%A9', "caf\u{00e9}"],
            'literal plus' => ['/test/a+b', 'a+b'],
            'encoded plus' => ['/test/a%2Bb', 'a+b'],
            'encoded slash' => ['/test/a%2Fb', 'a/b'],
            'decode once' => ['/test/a%2520b', 'a%20b'],
            'omitted optional' => ['/test', null],
        ];
    }

    /**
     * @return array<string, array{string, int|null, bool}>
     */
    public static function portProvider(): array
    {
        return [
            'http implicit default' => ['http://example.com/test', 80, true],
            'http explicit default' => ['http://example.com:80/test', 80, true],
            'http different port' => ['http://example.com:8080/test', 80, false],
            'http matching custom port' => ['http://example.com:8080/test', 8080, true],
            'http missing custom port' => ['http://example.com/test', 8080, false],
            'http unconstrained' => ['http://example.com:8080/test', null, true],
            'https implicit default' => ['https://example.com/test', 443, true],
            'https explicit default' => ['https://example.com:443/test', 443, true],
            'https different port' => ['https://example.com:8443/test', 443, false],
            'https matching custom port' => ['https://example.com:8443/test', 8443, true],
            'https missing custom port' => ['https://example.com/test', 8443, false],
            'https unconstrained' => ['https://example.com:8443/test', null, true],
        ];
    }

    public function testCheckMethodIgnored(): void
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

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->matchRequest($request)
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
                'uri' => '/test/a',
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->matchRequest($request)
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
                'uri' => '/invalid',
            ],
        ]);

        $this->assertNull(
            $route->matchRequest($request)
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
            $route->matchRequest($request)
        );
    }

    public function testCheckPathLiteralCharacters(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'files/archive.zip',
        ]);

        $matchingRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/files/archive.zip',
            ],
        ]);

        $invalidRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/files/archiveXzip',
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->matchRequest($matchingRequest)
        );

        $this->assertNull(
            $route->matchRequest($invalidRequest)
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

        $this->assertArraysAreIdentical(
            ['item' => 'id'],
            $route->getBindingFields()
        );

        $matchingRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/test/123',
            ],
        ]);

        $optionalRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/test',
            ],
        ]);

        $invalidRequest = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/test/value',
            ],
        ]);

        $this->assertInstanceOf(
            ServerRequest::class,
            $route->matchRequest($matchingRequest)
        );

        $optionalRequest = $route->matchRequest($optionalRequest);

        $this->assertInstanceOf(
            ServerRequest::class,
            $optionalRequest
        );

        $this->assertArraysAreIdentical(
            [
                'item' => null,
            ],
            $optionalRequest->getAttribute('routeArguments')
        );

        $this->assertNull(
            $route->matchRequest($invalidRequest)
        );
    }

    public function testCheckPathPlaceholderCaptures(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{a}/{b}',
            'placeholders' => [
                'a' => '(foo|bar)',
            ],
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/test/foo/tail',
            ],
        ]);

        $request = $route->matchRequest($request);

        $this->assertArraysAreIdentical(
            [
                'a' => 'foo',
                'b' => 'tail',
            ],
            $request?->getAttribute('routeArguments')
        );
    }

    #[DataProvider('pathArgumentProvider')]
    public function testCheckPathPlaceholderDecoding(string $uri, string|null $expected): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test/{value?}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => $uri,
            ],
        ]);

        $request = $route->matchRequest($request);

        $this->assertArraysAreIdentical(
            [
                'value' => $expected,
            ],
            $request?->getAttribute('routeArguments')
        );
    }

    public function testCheckPathPlaceholdersWithinSegment(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'files/{name}.{extension}',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => '/files/archive.zip',
            ],
        ]);

        $request = $route->matchRequest($request);

        $this->assertArraysAreIdentical(
            [
                'name' => null,
                'extension' => null,
            ],
            $route->getBindingFields()
        );

        $this->assertArraysAreIdentical(
            [
                'name' => 'archive',
                'extension' => 'zip',
            ],
            $request?->getAttribute('routeArguments')
        );
    }

    #[DataProvider('portProvider')]
    public function testCheckPort(string $uri, int|null $port, bool $expected): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'test',
            'port' => $port,
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => $uri,
            ],
        ]);

        $this->assertSame($expected, $route->matchRequest($request) !== null);
    }

    public function testGetBindingCallbacks(): void
    {
        $callback = static function(string $value): string {
            return $value;
        };

        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'bindingCallbacks' => [
                'item' => $callback,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [
                'item' => $callback,
            ],
            $route->getBindingCallbacks()
        );
    }

    public function testGetMethods(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'methods' => ['GET'],
        ]);

        $this->assertSame(
            ['GET'],
            $route->getMethods()
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

    public function testOptionalPlaceholderWithinSegmentInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Optional route placeholders must occupy an entire path segment.');

        $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'path' => 'files/{name?}.zip',
        ]);
    }

    public function testSetBindingCallback(): void
    {
        $callback = static function(string $value): string {
            return $value;
        };

        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setBindingCallback('item', $callback)
        );

        $this->assertArraysAreIdentical(
            [
                'item' => $callback,
            ],
            $route->getBindingCallbacks()
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

    public function testSetMethods(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setMethods(['POST'])
        );

        $this->assertSame(
            ['POST'],
            $route->getMethods()
        );
    }

    public function testSetMiddleware(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setMiddleware(['test'])
        );

        $this->assertArraysAreIdentical(
            ['test'],
            $route->getMiddleware()
        );
    }

    public function testSetPlaceholder(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setPlaceholder('id', '\\d+')
        );

        $this->assertArraysAreIdentical(
            [
                'id' => '\\d+',
            ],
            $route->getPlaceholders()
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
        $this->expectExceptionMessageIs('Route port must be between 1 and 65535.');

        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $route->setPort(0);
    }

    public function testSetPortInvalidMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Route port must be between 1 and 65535.');

        $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
            'port' => 65536,
        ]);
    }

    public function testSetScheme(): void
    {
        $route = $this->container->build(ControllerRoute::class, [
            'destination' => [TestController::class, 'test'],
        ]);

        $this->assertSame(
            $route,
            $route->setScheme('https')
        );

        $this->assertSame(
            'https',
            $route->getScheme()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
