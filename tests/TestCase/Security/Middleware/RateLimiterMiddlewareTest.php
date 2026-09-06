<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Middleware;

use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\TooManyRequestsException;
use Fyre\Http\ServerRequest;
use Fyre\Security\Middleware\RateLimiterMiddleware;
use Fyre\Security\RateLimiter\SlidingWindowRateLimiter;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class RateLimiterMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAllowedForwardsRequest(): void
    {
        $limiter = $this->createStub(SlidingWindowRateLimiter::class);
        $limiter->method('checkLimit')->willReturn([
            'allowed' => true,
            'limit' => 10,
            'remaining' => 9,
            'reset' => 1000,
        ]);
        $limiter->method('addHeaders')->willReturnArgument(0);

        $container = $this->createStub(Container::class);
        $container->method('build')->willReturn($limiter);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn(new ClientResponse());

        new RateLimiterMiddleware($container)->process($this->request, $handler);
    }

    public function testProcessAppliesLimiterResponse(): void
    {
        $data = [
            'allowed' => true,
            'limit' => 10,
            'remaining' => 9,
            'reset' => 1000,
        ];
        $response = new ClientResponse();
        $limitedResponse = $response->withHeader('X-RateLimit-Remaining', '9');

        $limiter = $this->createMock(SlidingWindowRateLimiter::class);
        $limiter->method('checkLimit')->willReturn($data);
        $limiter->expects($this->once())
            ->method('addHeaders')
            ->with($this->identicalTo($response), $data)
            ->willReturn($limitedResponse);

        $container = $this->createStub(Container::class);
        $container->method('build')->willReturn($limiter);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $limitedResponse,
            new RateLimiterMiddleware($container)->process($this->request, $handler)
        );
    }

    public function testProcessDeniedDoesNotForwardRequest(): void
    {
        $this->expectException(TooManyRequestsException::class);

        $limiter = $this->createStub(SlidingWindowRateLimiter::class);
        $limiter->method('checkLimit')->willReturn([
            'allowed' => false,
            'limit' => 10,
            'remaining' => 0,
            'reset' => 1000,
        ]);
        $limiter->method('getMessage')->willReturn('Rate limit exceeded');

        $container = $this->createStub(Container::class);
        $container->method('build')->willReturn($limiter);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        new RateLimiterMiddleware($container)->process($this->request, $handler);
    }

    public function testProcessSkippedForwardsRequest(): void
    {
        $limiter = $this->createMock(SlidingWindowRateLimiter::class);
        $limiter->method('shouldSkip')->willReturn(true);
        $limiter->expects($this->never())->method('checkLimit');

        $container = $this->createStub(Container::class);
        $container->method('build')->willReturn($limiter);

        $response = new ClientResponse();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn($response);

        $this->assertSame(
            $response,
            new RateLimiterMiddleware($container)->process($this->request, $handler)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class);
    }
}
