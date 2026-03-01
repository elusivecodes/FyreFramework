<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait CookieTrait
{
    public function testCookie(): void
    {
        $this->get('/cookie');

        $this->assertCookie('value', 'key');
    }

    public function testCookieFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that cookie "key" value is equal to "value".');

        $this->get('/response');

        $this->assertCookie('value', 'key');
    }

    public function testCookieNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertCookie('value', 'key');
    }
}
