<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\Security\Cors;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function class_uses;

final class CorsTest extends TestCase
{
    protected Container $container;

    public function testAddHeaders(): void
    {
        $cors = new Cors($this->container, [
            'allowCredentials' => true,
            'allowedOrigins' => ['https://test.com'],
            'exposedHeaders' => ['X-Test', 'X-Result'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeaders($request, new ClientResponse());

        $this->assertSame(
            'https://test.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertSame(
            'true',
            $response->getHeaderLine('Access-Control-Allow-Credentials')
        );
        $this->assertSame(
            'X-Test, X-Result',
            $response->getHeaderLine('Access-Control-Expose-Headers')
        );
        $this->assertSame(
            'Origin',
            $response->getHeaderLine('Vary')
        );
    }

    public function testAddHeadersDeniedOrigin(): void
    {
        $cors = new Cors($this->container, [
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://invalid.com',
                ],
            ],
        ]);

        $response = $cors->addHeaders($request, new ClientResponse());

        $this->assertSame(
            '',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertSame(
            'Origin',
            $response->getHeaderLine('Vary')
        );
    }

    public function testAddHeadersDisabled(): void
    {
        $cors = new Cors($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeaders($request, new ClientResponse());

        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
        $this->assertSame([], $response->getHeader('Vary'));
    }

    public function testAddHeadersPreflight(): void
    {
        $cors = new Cors($this->container, [
            'allowCredentials' => true,
            'allowedHeaders' => ['Content-Type', 'X-Test'],
            'allowedMethods' => ['GET', 'POST'],
            'allowedOrigins' => ['https://test.com'],
            'maxAge' => 600,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Headers' => 'content-type, x-test',
                    'Access-Control-Request-Method' => 'post',
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeadersPreflight($request, new ClientResponse());

        $this->assertSame(
            'https://test.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertSame(
            'true',
            $response->getHeaderLine('Access-Control-Allow-Credentials')
        );
        $this->assertSame(
            'GET, POST',
            $response->getHeaderLine('Access-Control-Allow-Methods')
        );
        $this->assertSame(
            'Content-Type, X-Test',
            $response->getHeaderLine('Access-Control-Allow-Headers')
        );
        $this->assertSame(
            '600',
            $response->getHeaderLine('Access-Control-Max-Age')
        );
        $this->assertSame(
            'Origin, Access-Control-Request-Method, Access-Control-Request-Headers',
            $response->getHeaderLine('Vary')
        );
    }

    public function testAddHeadersPreflightDenied(): void
    {
        $cors = new Cors($this->container, [
            'allowedHeaders' => ['Content-Type'],
            'allowedMethods' => ['POST'],
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Headers' => 'X-Test',
                    'Access-Control-Request-Method' => 'POST',
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeadersPreflight($request, new ClientResponse());

        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
        $this->assertSame(
            'Origin, Access-Control-Request-Method, Access-Control-Request-Headers',
            $response->getHeaderLine('Vary')
        );
    }

    public function testAddHeadersPreflightMissingRequestMethod(): void
    {
        $cors = new Cors($this->container, [
            'allowedMethods' => ['*'],
            'allowedOrigins' => ['*'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeadersPreflight($request, new ClientResponse());

        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
    }

    public function testAddHeadersPreflightWildcard(): void
    {
        $cors = new Cors($this->container, [
            'allowedHeaders' => ['*'],
            'allowedMethods' => ['*'],
            'allowedOrigins' => ['*'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Headers' => 'Content-Type, X-Test',
                    'Access-Control-Request-Method' => 'PATCH',
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeadersPreflight($request, new ClientResponse());

        $this->assertSame(
            '*',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertSame(
            'PATCH',
            $response->getHeaderLine('Access-Control-Allow-Methods')
        );
        $this->assertSame(
            'Content-Type, X-Test',
            $response->getHeaderLine('Access-Control-Allow-Headers')
        );
    }

    public function testAddHeadersPreflightWildcardOriginVary(): void
    {
        $cors = new Cors($this->container, [
            'allowedOrigins' => ['*'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Method' => 'POST',
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeadersPreflight($request, new ClientResponse());

        $this->assertSame(
            'Access-Control-Request-Method, Access-Control-Request-Headers',
            $response->getHeaderLine('Vary')
        );
    }

    public function testAddHeadersWildcardOrigin(): void
    {
        $cors = new Cors($this->container, [
            'allowedOrigins' => ['*'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $cors->addHeaders($request, new ClientResponse());

        $this->assertSame(
            '*',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
        $this->assertSame([], $response->getHeader('Vary'));
    }

    public function testCanHandleRequest(): void
    {
        $cors = new Cors($this->container, [
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $this->assertTrue($cors->canHandleRequest($request));
    }

    public function testCanHandleRequestDisabled(): void
    {
        $cors = new Cors($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $this->assertFalse($cors->canHandleRequest($request));
    }

    public function testCanHandleRequestMissingOrigin(): void
    {
        $cors = new Cors($this->container, [
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class);

        $this->assertFalse($cors->canHandleRequest($request));
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Cors::class)
        );
    }

    public function testIsPreflightRequest(): void
    {
        $cors = new Cors($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Method' => 'POST',
                ],
            ],
        ]);

        $this->assertTrue($cors->isPreflightRequest($request));
    }

    public function testIsPreflightRequestInvalidMethod(): void
    {
        $cors = new Cors($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'GET',
                'headers' => [
                    'Access-Control-Request-Method' => 'POST',
                ],
            ],
        ]);

        $this->assertFalse($cors->isPreflightRequest($request));
    }

    public function testIsPreflightRequestMissingRequestMethod(): void
    {
        $cors = new Cors($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
            ],
        ]);

        $this->assertFalse($cors->isPreflightRequest($request));
    }

    public function testShouldNotSkip(): void
    {
        $cors = new Cors($this->container, [
            'skipCheck' => static fn(ServerRequestInterface $request): bool => $request->getMethod() === 'GET',
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
            ],
        ]);

        $this->assertFalse($cors->shouldSkip($request));
    }

    public function testShouldSkip(): void
    {
        $cors = new Cors($this->container, [
            'skipCheck' => static fn(ServerRequestInterface $request): bool => $request->getMethod() === 'GET',
        ]);
        $request = $this->container->build(ServerRequest::class);

        $this->assertTrue($cors->shouldSkip($request));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
