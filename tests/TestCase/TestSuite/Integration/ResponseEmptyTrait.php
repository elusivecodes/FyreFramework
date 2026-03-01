<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait ResponseEmptyTrait
{
    public function testResponseEmpty(): void
    {
        $this->get('/empty');

        $this->assertResponseEmpty();
    }

    public function testResponseEmptyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body is empty.');

        $this->get('/response');

        $this->assertResponseEmpty();
    }

    public function testResponseEmptyNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseEmpty();
    }

    public function testResponseNotEmpty(): void
    {
        $this->get('/response');

        $this->assertResponseNotEmpty();
    }

    public function testResponseNotEmptyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body is not empty.');

        $this->get('/empty');

        $this->assertResponseNotEmpty();
    }

    public function testResponseNotEmptyNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertResponseNotEmpty();
    }
}
