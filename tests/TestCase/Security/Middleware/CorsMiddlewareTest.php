<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Middleware;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Factories\ResponseFactory;
use Fyre\Http\ServerRequest;
use Fyre\Security\Middleware\CorsMiddleware;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddlewareTest extends TestCase
{
    protected Container $container;

    protected ServerRequest $request;

    /**
     * @return array<string, array{array<int, string>}>
     */
    public static function preflightMethodsProvider(): array
    {
        return [
            'allowed method' => [['POST']],
            'denied method' => [['GET']],
        ];
    }

    public function testProcessDisabledForwardsRequest(): void
    {
        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            new CorsMiddleware($this->container)->process($this->request, $handler)
        );
    }

    public function testProcessForwardsRequest(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedOrigins' => ['https://test.com'],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn(new ClientResponse());

        $middleware->process($this->request, $handler);
    }

    /**
     * @param array<int, string> $allowedMethods
     */
    #[DataProvider('preflightMethodsProvider')]
    public function testProcessPreflightDoesNotForwardRequest(array $allowedMethods): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedMethods' => $allowedMethods,
            'allowedOrigins' => ['https://test.com'],
        ]);
        $request = $this->request
            ->withMethod('OPTIONS')
            ->withHeader('Access-Control-Request-Method', 'POST');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware->process($request, $handler);
    }

    public function testProcessSkippedForwardsRequest(): void
    {
        $middleware = new CorsMiddleware($this->container, [
            'allowedOrigins' => ['https://test.com'],
            'skipCheck' => static fn(): bool => true,
        ]);

        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            $middleware->process($this->request, $handler)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(ResponseFactoryInterface::class, ResponseFactory::class);

        $this->request = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'Origin' => 'https://test.com',
                ],
            ],
        ]);
    }
}
