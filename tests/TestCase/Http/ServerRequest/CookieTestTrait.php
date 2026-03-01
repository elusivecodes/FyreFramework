<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\DateTime;

trait CookieTestTrait
{
    public function testGetCookie(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'cookies' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getCookie('test')
        );
    }

    public function testGetCookieAll(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'cookies' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request->getCookie()
        );
    }

    public function testGetCookieInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getCookie('invalid')
        );
    }

    public function testGetCookieType(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'cookies' => [
                'test' => '2024-12-31',
            ],
        ]);

        $value = $request->getCookie('test', 'date');

        $this->assertInstanceOf(
            DateTime::class,
            $value
        );

        $this->assertSame(
            '2024-12-31T00:00:00.000+00:00',
            $value->toISOString()
        );
    }

    public function testWithCookieParams(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withCookieParams(['test' => 'value']);

        $this->assertEmpty(
            $request1->getCookieParams()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request2->getCookieParams()
        );
    }
}
