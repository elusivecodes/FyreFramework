<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Middleware;

use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\Security\ContentSecurityPolicy;
use Fyre\Security\Middleware\CspMiddleware;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class CspMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAppliesPolicyResponse(): void
    {
        $response = new ClientResponse();
        $policyResponse = $response->withHeader('Content-Security-Policy', "default-src 'self';");

        $csp = $this->createMock(ContentSecurityPolicy::class);
        $csp->expects($this->once())
            ->method('addHeaders')
            ->with($this->identicalTo($response))
            ->willReturn($policyResponse);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $policyResponse,
            new CspMiddleware($csp)->process($this->request, $handler)
        );
    }

    public function testProcessForwardsRequest(): void
    {
        $csp = $this->createStub(ContentSecurityPolicy::class);
        $csp->method('addHeaders')->willReturnArgument(0);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($this->request))
            ->willReturn(new ClientResponse());

        new CspMiddleware($csp)->process($this->request, $handler);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class);
    }
}
