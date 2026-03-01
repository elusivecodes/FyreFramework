<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait HeaderTrait
{
    public function testHeader(): void
    {
        $this->get('/header');

        $this->assertHeader('This is a header value', 'Name');
    }

    public function testHeaderFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Name" value is equal to "invalid".');

        $this->get('/header');

        $this->assertHeader('invalid', 'Name');
    }

    public function testHeaderNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertHeader('invalid', 'Name');
    }
}
