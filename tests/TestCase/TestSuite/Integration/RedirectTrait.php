<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait RedirectTrait
{
    public function testNoRedirect(): void
    {
        $this->get('/response');

        $this->assertNoRedirect();
    }

    public function testNoRedirectFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Location" is not set.');

        $this->get('/redirect');

        $this->assertNoRedirect();
    }

    public function testNoRedirectNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertNoRedirect();
    }

    public function testRedirect(): void
    {
        $this->get('/redirect');

        $this->assertRedirect();
    }

    public function testRedirectFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Location" is set.');

        $this->get('/response');

        $this->assertRedirect();
    }

    public function testRedirectNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertRedirect();
    }
}
