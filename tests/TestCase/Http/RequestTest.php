<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\Message;
use Fyre\Http\Request;
use Fyre\Http\Stream;
use Fyre\Http\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $uri = new Uri('https://test.com/path?a=1&b=2');

        $request = new Request($uri, [
            'method' => 'post',
            'body' => 'test',
            'headers' => [
                'test' => 'value',
            ],
            'protocolVersion' => '2.0',
        ]);

        $this->assertSame(
            $uri,
            $request->getUri()
        );

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $body = $request->getBody();

        $this->assertInstanceOf(
            Stream::class,
            $body
        );

        $this->assertSame(
            'test',
            $body->getContents()
        );

        $this->assertSame(
            [
                'value',
            ],
            $request->getHeader('test')
        );

        $this->assertSame(
            [
                'test.com',
            ],
            $request->getHeader('host')
        );

        $this->assertSame(
            '/path?a=1&b=2',
            $request->getRequestTarget()
        );

        $this->assertSame(
            '2.0',
            $request->getProtocolVersion()
        );
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
        $this->expectExceptionMessage('HTTP method `INVALID` is not valid.');

        $request = new Request();
        $request->withMethod('invalid');
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

        $this->assertSame(
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

        $this->assertSame(
            [
                'test.com',
            ],
            $request2->getHeader('host')
        );
    }
}
