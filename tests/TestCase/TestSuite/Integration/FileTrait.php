<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait FileTrait
{
    public function testFileResponse(): void
    {
        $this->get('/download');

        $this->assertFileResponse('tests/assets/test.jpg');
    }

    public function testFileResponseFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that download response file is equal to "tests/assets/test.jpg".');

        $this->get('/response');

        $this->assertFileResponse('tests/assets/test.jpg');
    }

    public function testFileResponseNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('No response has been set.');

        $this->assertFileResponse('invalid');
    }
}
