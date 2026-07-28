<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Cookie;

use Fyre\Http\Client\Response;
use Fyre\Http\Cookie\Cookie;
use Fyre\Http\Cookie\CookieJar;
use Fyre\Http\Uri;
use PHPUnit\Framework\TestCase;

use function time;

final class CookieJarTest extends TestCase
{
    public function testAddExpired(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);
        $expiredCookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'expires' => time(),
            'hostOnly' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $cookieJar->add($expiredCookie);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeader(): void
    {
        $matchingCookie = new Cookie('matching', 'value', [
            'domain' => 'example.com',
            'path' => '/path',
        ]);
        $domainCookie = new Cookie('domain', 'value', [
            'domain' => 'other.com',
        ]);
        $pathCookie = new Cookie('path', 'value', [
            'domain' => 'example.com',
            'path' => '/other',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($matchingCookie);
        $cookieJar->add($domainCookie);
        $cookieJar->add($pathCookie);

        $uri = new Uri('https://example.com/path/test');

        $this->assertSame(
            'matching=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderDomain(): void
    {
        $hostOnlyCookie = new Cookie('host', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);
        $domainCookie = new Cookie('domain', 'value', [
            'domain' => 'example.com',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($hostOnlyCookie);
        $cookieJar->add($domainCookie);

        $uri = new Uri('https://sub.example.com');

        $this->assertSame(
            'domain=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderPath(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/path/',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $matchingUri = new Uri('https://example.com/path/test');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/pathname');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testGetHeaderPathRepeatedSlash(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/path//',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $matchingUri = new Uri('https://example.com/path//test');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/path/test');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testGetHeaderPreservesOpaqueValues(): void
    {
        $cookie = new Cookie('test', 'a+b%2Bc=');

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com');

        $this->assertSame(
            'test=a+b%2Bc=',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderSecure(): void
    {
        $cookie = new Cookie('secure', 'value', [
            'domain' => 'example.com',
            'secure' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $httpUri = new Uri('http://example.com');

        $this->assertSame(
            '',
            $cookieJar->getHeader($httpUri)
        );

        $httpsUri = new Uri('https://example.com');

        $this->assertSame(
            'secure=value',
            $cookieJar->getHeader($httpsUri)
        );
    }

    public function testGetHeaderTrailingDotHost(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com.');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $invalidUri = new Uri('https://example.com..');

        $this->assertSame(
            '',
            $cookieJar->getHeader($invalidUri)
        );
    }

    public function testStoreResponse(): void
    {
        $uri = new Uri('https://example.com/path');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $otherUri = new Uri('https://other.com/path');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testStoreResponseDefaultPath(): void
    {
        $uri = new Uri('https://example.com/path/page');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $matchingUri = new Uri('https://example.com/path/other');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/other');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testStoreResponseDefaultPathTrailingSlash(): void
    {
        $uri = new Uri('https://example.com/path/page/');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $matchingUri = new Uri('https://example.com/path/page/other');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/path/other');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testStoreResponseInvalidDomain(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=example.com.',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseInvalidHeader(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'invalid name=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseOtherDomain(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=other.com',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseSecure(): void
    {
        $uri = new Uri('http://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Secure',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }
}
