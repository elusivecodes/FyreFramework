<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ClientResponse;

use Fyre\Http\ClientResponse;
use Fyre\Http\Cookie;

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

        $this->assertSame(
            'value',
            $response2->getCookie('test')->getValue()
        );
    }

    public function testWithCookieExpires(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value', time() + 60);

        $this->assertFalse(
            $response2->getCookie('test')->isExpired()
        );
    }

    public function testWithCookieOptions(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withCookie('test', 'value', domain: 'test.com');

        $this->assertSame(
            'test.com',
            $response2->getCookie('test')->getDomain()
        );
    }

    public function testWithExpiredCookie(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withExpiredCookie('test');

        $this->assertNull(
            $response1->getCookie('test')
        );

        $this->assertTrue(
            $response2->getCookie('test')->isExpired()
        );
    }
}
