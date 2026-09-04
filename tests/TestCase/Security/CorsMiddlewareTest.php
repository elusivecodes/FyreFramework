<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Factories\ResponseFactory;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Security\Middleware\CorsMiddleware;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function class_uses;

final class CorsMiddlewareTest extends TestCase
{
    protected Config $config;

    protected Container $container;

    public function testConfigFallback(): void
    {
        $this->config->set('Cors.allowedOrigins', ['https://test.com']);
        $middleware = new CorsMiddleware($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $this->handle($middleware, $request);

        $this->assertSame(
            'https://test.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CorsMiddleware::class)
        );
    }

    public function testDisabled(): void
    {
        $middleware = new CorsMiddleware($this->container);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $this->handle($middleware, $request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
    }

    public function testPreflightRequest(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedHeaders' => ['Content-Type'],
            'allowedMethods' => ['POST'],
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'method' => 'OPTIONS',
                'headers' => [
                    'Access-Control-Request-Headers' => 'Content-Type',
                    'Access-Control-Request-Method' => 'POST',
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $this->handle($middleware, $request);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame(
            'https://test.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function testPreflightRequestDenied(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedMethods' => ['GET'],
            'allowedOrigins' => ['https://test.com'],
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

        $response = $this->handle($middleware, $request);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
    }

    public function testRequest(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $this->handle($middleware, $request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            'https://test.com',
            $response->getHeaderLine('Access-Control-Allow-Origin')
        );
    }

    public function testSkipCheck(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedOrigins' => ['https://test.com'],
            'skipCheck' => static fn(): bool => true,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);

        $response = $this->handle($middleware, $request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $response->getHeader('Access-Control-Allow-Origin'));
    }

    protected function handle(CorsMiddleware $middleware, ServerRequestInterface $request): ClientResponse
    {
        $queue = new MiddlewareQueue([
            $middleware,
            static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ClientResponse => new ClientResponse([
                'statusCode' => 200,
            ]),
        ]);
        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $response = $handler->handle($request);

        $this->assertInstanceOf(ClientResponse::class, $response);

        return $response;
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(ResponseFactoryInterface::class, ResponseFactory::class);

        $this->config = $this->container->use(Config::class);
    }
}
