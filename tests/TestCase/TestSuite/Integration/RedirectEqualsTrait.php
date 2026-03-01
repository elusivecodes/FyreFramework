<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait RedirectEqualsTrait
{
    public function testRedirectEquals(): void
    {
        $this->get('/redirect');

        $this->assertRedirectEquals('/test');
    }

    public function testRedirectEqualsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Location" value is equal to "/invalid".');

        $this->get('/redirect');

        $this->assertRedirectEquals('/invalid');
    }

    public function testRedirectEqualsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertRedirectEquals('/test');
    }
}
