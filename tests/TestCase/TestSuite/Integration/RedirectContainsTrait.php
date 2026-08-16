<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait RedirectContainsTrait
{
    public function testRedirectContains(): void
    {
        $this->get('/redirect');

        $this->assertRedirectContains('test');
    }

    public function testRedirectContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageIs('Failed asserting that header "Location" value contains "invalid".');

        $this->get('/redirect');

        $this->assertRedirectContains('invalid');
    }

    public function testRedirectContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageIs('No response has been set.');

        $this->assertRedirectContains('test');
    }

    public function testRedirectNotContains(): void
    {
        $this->get('/redirect');

        $this->assertRedirectNotContains('invalid');
    }

    public function testRedirectNotContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageIs('Failed asserting that header "Location" value does not contain "test".');

        $this->get('/redirect');

        $this->assertRedirectNotContains('test');
    }

    public function testRedirectNotContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageIs('No response has been set.');

        $this->assertRedirectNotContains('test');
    }
}
