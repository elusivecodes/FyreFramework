<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait ContentTypeTrait
{
    public function testContentType(): void
    {
        $this->get('/response');

        $this->assertContentType('text/html');
    }

    public function testContentTypeFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response content type is equal to "text/xml".');

        $this->get('/response');

        $this->assertContentType('text/xml');
    }

    public function testContentTypeNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertContentType('text/xml');
    }
}
