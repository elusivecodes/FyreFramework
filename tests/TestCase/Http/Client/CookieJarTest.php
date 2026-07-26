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
        $cookieJar = new CookieJar();
        $cookieJar->add(new Cookie('matching', 'value', [
            'domain' => 'example.com',
            'path' => '/path',
        ]));
        $cookieJar->add(new Cookie('domain', 'value', [
            'domain' => 'other.com',
        ]));
        $cookieJar->add(new Cookie('path', 'value', [
            'domain' => 'example.com',
            'path' => '/other',
        ]));

        $this->assertSame(
            'matching=value',
            $cookieJar->getHeader(new Uri('https://example.com/path/test'))
        );
    }

    public function testGetHeaderSecure(): void
    {
        $cookieJar = new CookieJar();
        $cookieJar->add(new Cookie('secure', 'value', [
            'domain' => 'example.com',
            'secure' => true,
        ]));

        $this->assertSame(
            '',
            $cookieJar->getHeader(new Uri('http://example.com'))
        );

        $this->assertSame(
            'secure=value',
            $cookieJar->getHeader(new Uri('https://example.com'))
        );
    }

    public function testStoreResponse(): void
    {
        $cookieJar = new CookieJar();
        $cookieJar->storeResponse(
            new Uri('https://example.com/path'),
            new Response([
                'headers' => [
                    'Set-Cookie' => 'test=value',
                ],
            ])
        );

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader(new Uri('https://example.com/path'))
        );

        $this->assertSame(
            '',
            $cookieJar->getHeader(new Uri('https://other.com/path'))
        );
    }
}
