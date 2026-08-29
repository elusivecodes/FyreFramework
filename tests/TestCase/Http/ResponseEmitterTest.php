<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie\Cookie;
use Fyre\Http\ResponseEmitter;
use Fyre\Http\Stream;
use Fyre\Http\Stream\IterableStream;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function class_uses;
use function fclose;
use function fwrite;
use function ob_get_clean;
use function ob_start;
use function stream_socket_pair;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

final class ResponseEmitterTest extends TestCase
{
    /**
     * @var array<string, mixed>[]
     */
    protected static array $cookies = [];

    /**
     * @var string[]
     */
    protected static array $headers = [];

    protected ResponseEmitter $emitter;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ResponseEmitter::class)
        );
    }

    public function testEmit(): void
    {
        $response = new ClientResponse()
            ->withHeader('X-Test', 'test')
            ->withCookie('session', 'abc123');
        $response->getBody()->write('This is a test.');

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertArraysAreIdentical(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/html; charset=UTF-8',
                'X-Test: test',
            ],
            self::$headers
        );

        $this->assertArraysAreIdentical(
            [
                [
                    'name' => 'session',
                    'value' => 'abc123',
                    'expires' => null,
                    'path' => '/',
                    'domain' => '',
                    'secure' => false,
                    'httpOnly' => false,
                    'sameSite' => 'lax',
                ],
            ],
            self::$cookies
        );

        $this->assertSame(
            'This is a test.',
            $output
        );
    }

    public function testEmitHead(): void
    {
        $response = new ClientResponse(['body' => 'This is a test.']);
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('HEAD');

        ob_start();
        $this->emitter->emit($response, $request);
        $output = ob_get_clean();

        $this->assertArraysAreIdentical(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/html; charset=UTF-8',
            ],
            self::$headers
        );

        $this->assertSame('', $output);
    }

    public function testEmitIterableStream(): void
    {
        $response = new ClientResponse([
            'body' => new IterableStream(['This ', 'is ', 'a test.']),
        ]);

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertSame('This is a test.', $output);
    }

    public function testEmitNonSeekable(): void
    {
        $sockets = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );

        $this->assertNotFalse($sockets);

        [$reader, $writer] = $sockets;

        fwrite($writer, 'This is a test.');
        fclose($writer);

        $response = new ClientResponse([
            'body' => new Stream($reader),
        ]);

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertSame(
            'This is a test.',
            $output
        );
    }

    public function testEmitRange(): void
    {
        $response = new ClientResponse()
            ->withContentType('text/plain')
            ->withStatus(206)
            ->withHeader('Content-Range', 'bytes 5-10/15');
        $response->getBody()->write('This is a test.');

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertArraysAreIdentical(
            [
                'HTTP/1.1 206 Partial Content',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Range: bytes 5-10/15',
            ],
            self::$headers
        );

        $this->assertArraysAreIdentical(
            [],
            self::$cookies
        );

        $this->assertSame(
            'is a t',
            $output
        );
    }

    public function testEmitRangeComplete(): void
    {
        $response = new ClientResponse()
            ->withContentType('text/plain')
            ->withHeader('Content-Range', 'bytes 0-20/15');
        $response->getBody()->write('This is a test.');

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertArraysAreIdentical(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Range: bytes 0-20/15',
            ],
            self::$headers
        );

        $this->assertArraysAreIdentical(
            [],
            self::$cookies
        );

        $this->assertSame(
            'This is a test.',
            $output
        );
    }

    public function testEmitRangeNonSeekable(): void
    {
        $sockets = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );

        $this->assertNotFalse($sockets);

        [$reader, $writer] = $sockets;

        fwrite($writer, 'This is a test.');
        fclose($writer);

        $response = new ClientResponse([
            'body' => new Stream($reader),
            'headers' => [
                'Content-Range' => 'bytes 5-10/15',
            ],
        ]);

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertSame(
            'is a t',
            $output
        );
    }

    public function testEmitSetCookieHeader(): void
    {
        $response = new ClientResponse()
            ->withHeader('Set-Cookie', 'session=abc123; path=/; samesite=lax');
        $response->getBody()->write('This is a test.');

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertArraysAreIdentical(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/html; charset=UTF-8',
            ],
            self::$headers
        );

        $this->assertArraysAreIdentical(
            [
                [
                    'name' => 'session',
                    'value' => 'abc123',
                    'expires' => null,
                    'path' => '/',
                    'domain' => '',
                    'secure' => false,
                    'httpOnly' => false,
                    'sameSite' => 'lax',
                ],
            ],
            self::$cookies
        );

        $this->assertSame(
            'This is a test.',
            $output
        );
    }

    public function testEmitWithoutBodyStatus(): void
    {
        foreach ([100, 199, 204, 304] as $statusCode) {
            $response = new ClientResponse([
                'body' => 'This is a test.',
                'statusCode' => $statusCode,
            ]);

            ob_start();
            $this->emitter->emit($response);
            $output = ob_get_clean();

            $this->assertSame('', $output);
        }
    }

    #[Override]
    protected function setUp(): void
    {
        self::$headers = [];
        self::$cookies = [];

        $this->emitter = $this->getStubBuilder(ResponseEmitter::class)
            ->onlyMethods(['setHeader', 'setCookie'])
            ->getStub();

        $this->emitter
            ->method('setHeader')
            ->willReturnCallback(static function(string $header, bool $replace = true): void {
                self::$headers[] = $header;
            });

        $this->emitter
            ->method('setCookie')
            ->willReturnCallback(static function(Cookie $cookie): void {
                self::$cookies[] = [
                    'name' => $cookie->getName(),
                    'value' => $cookie->getValue(),
                    'expires' => $cookie->getExpires(),
                    'path' => $cookie->getPath(),
                    'domain' => $cookie->getDomain(),
                    'secure' => $cookie->isSecure(),
                    'httpOnly' => $cookie->isHttpOnly(),
                    'sameSite' => $cookie->getSameSite(),
                ];
            });
    }
}
