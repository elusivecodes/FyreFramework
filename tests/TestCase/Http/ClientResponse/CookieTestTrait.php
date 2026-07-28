<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ClientResponse;

use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie\Cookie;

use function time;

trait CookieTestTrait
{
    public function testGetCookie(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value');

        $this->assertNull(
            $response1->getCookie('test')
        );

        $this->assertInstanceOf(
            Cookie::class,
            $response2->getCookie('test')
        );
    }

    public function testHasCookie(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value');

        $this->assertFalse(
            $response1->hasCookie('test')
        );

        $this->assertTrue(
            $response2->hasCookie('test')
        );
    }

    public function testHasCookieInvalid(): void
    {
        $response = new ClientResponse();

        $this->assertFalse(
            $response->hasCookie('test')
        );
    }

    public function testWithCookie(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value');
        $cookie = $response2->getCookie('test');

        $this->assertInstanceOf(
            Cookie::class,
            $cookie
        );

        $this->assertSame(
            'value',
            $cookie->getValue()
        );
    }

    public function testWithCookieExpires(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value', time() + 60);
        $cookie = $response2->getCookie('test');

        $this->assertInstanceOf(
            Cookie::class,
            $cookie
        );

        $this->assertFalse(
            $cookie->isExpired()
        );
    }

    public function testWithCookieOptions(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value', domain: 'test.com');
        $cookie = $response2->getCookie('test');

        $this->assertInstanceOf(
            Cookie::class,
            $cookie
        );

        $this->assertSame(
            'test.com',
            $cookie->getDomain()
        );
    }

    public function testWithExpiredCookie(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withExpiredCookie('test');
        $cookie = $response2->getCookie('test');

        $this->assertNull(
            $response1->getCookie('test')
        );

        $this->assertInstanceOf(
            Cookie::class,
            $cookie
        );

        $this->assertTrue(
            $cookie->isExpired()
        );
    }
}
