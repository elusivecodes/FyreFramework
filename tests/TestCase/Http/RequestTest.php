<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\Message;
use Fyre\Http\Request;
use Fyre\Http\Stream;
use Fyre\Http\Uri;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected Request $request;

    protected Uri $uri;

    public function testConstructorBody(): void
    {
        $body = $this->request->getBody();

        $this->assertInstanceOf(Stream::class, $body);
        $this->assertSame('test', $body->getContents());
    }

    public function testConstructorHeaders(): void
    {
        $this->assertArraysAreIdentical(
            ['value'],
            $this->request->getHeader('test')
        );
    }

    public function testConstructorHostHeader(): void
    {
        $this->assertArraysAreIdentical(
            ['test.com'],
            $this->request->getHeader('host')
        );
    }

    public function testConstructorMethod(): void
    {
        $this->assertSame('POST', $this->request->getMethod());
    }

    public function testConstructorProtocolVersion(): void
    {
        $this->assertSame('2.0', $this->request->getProtocolVersion());
    }

    public function testConstructorRequestTarget(): void
    {
        $this->assertSame(
            '/path?a=1&b=2',
            $this->request->getRequestTarget()
        );
    }

    public function testConstructorUri(): void
    {
        $this->assertSame($this->uri, $this->request->getUri());
    }

    public function testGetMethod(): void
    {
        $request = new Request();

        $this->assertSame(
            'GET',
            $request->getMethod()
        );
    }

    public function testGetUri(): void
    {
        $request = new Request();

        $this->assertInstanceOf(
            Uri::class,
            $request->getUri()
        );
    }

    public function testMessage(): void
    {
        $request = new Request();

        $this->assertInstanceOf(
            Message::class,
            $request
        );
    }

    public function testWithMethod(): void
    {
        $request1 = new Request();
        $request2 = $request1->withMethod('post');

        $this->assertNotSame(
            $request1,
            $request2
        );

        $this->assertSame(
            'POST',
            $request2->getMethod()
        );
    }

    public function testWithMethodInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('HTTP method `invalid method` is not valid.');

        $request = new Request();
        $request->withMethod('invalid method');
    }

    public function testWithRequestTarget(): void
    {
        $request1 = new Request();
        $request2 = $request1->withRequestTarget('/new-target');

        $this->assertNotSame(
            $request1,
            $request2
        );

        $this->assertSame(
            '/new-target',
            $request2->getRequestTarget()
        );
    }

    public function testWithUri(): void
    {
        $uri1 = new Uri();
        $uri2 = new Uri();

        $request1 = new Request($uri1);
        $request2 = $request1->withUri($uri2);

        $this->assertNotSame(
            $request1,
            $request2
        );

        $this->assertSame(
            $uri2,
            $request2->getUri()
        );
    }

    public function testWithUriPreserveHost(): void
    {
        $uri1 = new Uri('https://example.com');
        $uri2 = new Uri('https://test.com');

        $request1 = new Request($uri1);
        $request2 = $request1->withUri($uri2, true);

        $this->assertNotSame(
            $request1,
            $request2
        );

        $this->assertArraysAreIdentical(
            [
                'example.com',
            ],
            $request2->getHeader('host')
        );
    }

    public function testWithUriUpdateHost(): void
    {
        $uri1 = new Uri('https://example.com');
        $uri2 = new Uri('https://test.com');

        $request1 = new Request($uri1);
        $request2 = $request1->withUri($uri2, false);

        $this->assertNotSame(
            $request1,
            $request2
        );

        $this->assertArraysAreIdentical(
            [
                'test.com',
            ],
            $request2->getHeader('host')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->uri = new Uri('https://test.com/path?a=1&b=2');
        $this->request = new Request($this->uri, [
            'method' => 'post',
            'body' => 'test',
            'headers' => [
                'test' => 'value',
            ],
            'protocolVersion' => '2.0',
        ]);
    }
}
