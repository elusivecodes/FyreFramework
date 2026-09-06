<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Middleware;

use Fyre\Core\Container;
use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\Security\CsrfProtection;
use Fyre\Security\Exceptions\CsrfTokenException;
use Fyre\Security\Middleware\CsrfProtectionMiddleware;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class CsrfProtectionMiddlewareTest extends TestCase
{
    protected ServerRequest $request;

    public function testProcessAppliesProtectionResponse(): void
    {
        $response = new ClientResponse();
        $protectedResponse = $response->withHeader('Set-Cookie', 'CsrfToken=test');

        $csrfProtection = $this->createMock(CsrfProtection::class);
        $checkedRequest = $this->request->withAttribute('csrf', $csrfProtection);
        $csrfProtection->method('checkToken')->willReturn($checkedRequest);
        $csrfProtection->expects($this->once())
            ->method('beforeResponse')
            ->with($this->identicalTo($checkedRequest), $this->identicalTo($response))
            ->willReturn($protectedResponse);

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->assertSame(
            $protectedResponse,
            new CsrfProtectionMiddleware($csrfProtection)->process($this->request, $handler)
        );
    }

    public function testProcessForwardsCheckedRequest(): void
    {
        $csrfProtection = $this->createStub(CsrfProtection::class);
        $checkedRequest = $this->request->withAttribute('csrf', $csrfProtection);
        $csrfProtection->method('checkToken')->willReturn($checkedRequest);
        $csrfProtection->method('beforeResponse')->willReturnArgument(1);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($checkedRequest))
            ->willReturn(new ClientResponse());

        new CsrfProtectionMiddleware($csrfProtection)->process($this->request, $handler);
    }

    public function testProcessInvalidTokenDoesNotForwardRequest(): void
    {
        $this->expectException(CsrfTokenException::class);

        $csrfProtection = $this->createStub(CsrfProtection::class);
        $csrfProtection->method('checkToken')->willThrowException(new CsrfTokenException());

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        new CsrfProtectionMiddleware($csrfProtection)->process($this->request, $handler);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->request = new Container()->build(ServerRequest::class, [
            'options' => [
                'method' => 'POST',
            ],
        ]);
    }
}
