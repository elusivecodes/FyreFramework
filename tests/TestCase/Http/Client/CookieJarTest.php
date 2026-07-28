<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Fyre\Http\Client\CookieJar;
use Fyre\Http\Client\Response;
use Fyre\Http\Cookie;
use Fyre\Http\Uri;
use PHPUnit\Framework\TestCase;

final class CookieJarTest extends TestCase
{
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
}
