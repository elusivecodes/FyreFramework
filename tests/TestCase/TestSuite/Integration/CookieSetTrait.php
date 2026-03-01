<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait CookieSetTrait
{
    public function testCookieIsSet(): void
    {
        $this->get('/cookie');

        $this->assertCookieIsSet('key');
    }

    public function testCookieIsSetFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that cookie "key" is set.');

        $this->get('/response');

        $this->assertCookieIsSet('key');
    }

    public function testCookieIsSetNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertCookieIsSet('key');
    }

    public function testCookieNotSet(): void
    {
        $this->get('/response');

        $this->assertCookieNotSet('key');
    }

    public function testCookieNotSetFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that cookie "key" is not set.');

        $this->get('/cookie');

        $this->assertCookieNotSet('key');
    }

    public function testCookieNotSetNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertCookieNotSet('key');
    }
}
