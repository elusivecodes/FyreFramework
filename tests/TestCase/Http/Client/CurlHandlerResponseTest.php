<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Closure;
use Fyre\Http\Client\Handlers\CurlHandler;
use Fyre\Http\Client\Response;
use Override;
use PHPUnit\Framework\TestCase;

use function strlen;

final class CurlHandlerResponseTest extends TestCase
{
    protected Response $response;

    public function testBody(): void
    {
        $this->assertSame('body', $this->response->getBody()->getContents());
    }

    public function testDuplicateHeaders(): void
    {
        $this->assertArraysAreIdentical(
            ['first=1', 'second=2'],
            $this->response->getHeader('Set-Cookie')
        );
    }

    public function testFinalHeaders(): void
    {
        $this->assertSame('value', $this->response->getHeaderLine('X-Test'));
    }

    public function testInterimHeadersDiscarded(): void
    {
        $this->assertFalse($this->response->hasHeader('X-Interim'));
    }

    public function testProtocolVersion(): void
    {
        $this->assertSame('2.0', $this->response->getProtocolVersion());
    }

    public function testReasonPhrase(): void
    {
        $this->assertSame('Created', $this->response->getReasonPhrase());
    }

    public function testStatusCode(): void
    {
        $this->assertSame(201, $this->response->getStatusCode());
    }

    #[Override]
    protected function setUp(): void
    {
        $headers = "HTTP/1.1 100 Continue\r\nX-Interim: discard\r\n\r\n".
            "HTTP/2 201 Created\r\nX-Test: value\r\nSet-Cookie: first=1\r\nSet-Cookie: second=2\r\n\r\n";

        $this->response = Closure::bind(
            static fn(): Response => CurlHandler::buildResponse($headers.'body', strlen($headers)),
            null,
            CurlHandler::class
        )();
    }
}
