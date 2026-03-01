<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait ResponseEqualsTrait
{
    public function testResponseEquals(): void
    {
        $this->get('/response');

        $this->assertResponseEquals('This is a test response');
    }

    public function testResponseEqualsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body is equal to \'invalid\'.');

        $this->get('/response');

        $this->assertResponseEquals('invalid');
    }

    public function testResponseEqualsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseEquals('invalid');
    }

    public function testResponseNotEquals(): void
    {
        $this->get('/response');

        $this->assertResponseNotEquals('invalid');
    }

    public function testResponseNotEqualsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body is not equal to \'This is a test response\'.');

        $this->get('/response');

        $this->assertResponseNotEquals('This is a test response');
    }

    public function testResponseNotEqualsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseNotEquals('This is a test response');
    }
}
