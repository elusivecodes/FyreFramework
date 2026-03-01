<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie;
use Fyre\Http\ResponseEmitter;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

use function assert;
use function ob_get_clean;
use function ob_start;

final class ResponseEmitterTest extends TestCase
{
    protected static array $cookies = [];

    protected static array $headers = [];

    protected ResponseEmitter $emitter;

    public function testEmit(): void
    {
        $response = new ClientResponse()
            ->withHeader('X-Test', 'test')
            ->withCookie('session', 'abc123');
        $response->getBody()->write('This is a test.');

        ob_start();
        $this->emitter->emit($response);
        $output = ob_get_clean();

        $this->assertSame(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/html; charset=UTF-8',
                'X-Test: test',
            ],
            self::$headers
        );

        $this->assertSame(
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

        $this->assertSame(
            [
                'HTTP/1.1 206 Partial Content',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Range: bytes 5-10/15',
            ],
            self::$headers
        );

        $this->assertSame(
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

        $this->assertSame(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Range: bytes 0-20/15',
            ],
            self::$headers
        );

        $this->assertSame(
            [],
            self::$cookies
        );

        $this->assertSame(
            'This is a test.',
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

        $this->assertSame(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: text/html; charset=UTF-8',
            ],
            self::$headers
        );

        $this->assertSame(
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

    #[Override]
    protected function setUp(): void
    {
        self::$headers = [];
        self::$cookies = [];

        $this->emitter = $this->getStubBuilder(ResponseEmitter::class)
            ->onlyMethods(['setHeader', 'setCookie'])
            ->getStub();

        assert($this->emitter instanceof Stub);

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
