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
        $this->expectExceptionMessage('Failed asserting that header "Location" value contains "invalid".');

        $this->get('/redirect');

        $this->assertRedirectContains('invalid');
    }

    public function testRedirectContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertRedirectContains('test');
    }
}
