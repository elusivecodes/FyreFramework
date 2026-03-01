<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait ResponseContainsTrait
{
    public function testResponseContains(): void
    {
        $this->get('/response');

        $this->assertResponseContains('a test');
    }

    public function testResponseContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body contains "invalid".');

        $this->get('/response');

        $this->assertResponseContains('invalid');
    }

    public function testResponseContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseContains('invalid');
    }

    public function testResponseNotContains(): void
    {
        $this->get('/response');

        $this->assertResponseNotContains('invalid');
    }

    public function testResponseNotContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body does not contain "a test".');

        $this->get('/response');

        $this->assertResponseNotContains('a test');
    }

    public function testResponseNotContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseNotContains('a test');
    }
}
