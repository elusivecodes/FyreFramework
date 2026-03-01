<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait HeaderContainsTrait
{
    public function testHeaderContains(): void
    {
        $this->get('/header');

        $this->assertHeaderContains('a header', 'Name');
    }

    public function testHeaderContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Name" value contains "invalid".');

        $this->get('/header');

        $this->assertHeaderContains('invalid', 'Name');
    }

    public function testHeaderContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertHeaderContains('invalid', 'Name');
    }

    public function testHeaderNotContains(): void
    {
        $this->get('/header');

        $this->assertHeaderNotContains('invalid', 'Name');
    }

    public function testHeaderNotContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that header "Name" value does not contain "a header".');

        $this->get('/header');

        $this->assertHeaderNotContains('a header', 'Name');
    }

    public function testHeaderNotContainsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertHeaderNotContains('a header', 'Name');
    }
}
