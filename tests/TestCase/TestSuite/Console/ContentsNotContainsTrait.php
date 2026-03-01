<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use PHPUnit\Framework\AssertionFailedError;

use function fwrite;

use const PHP_EOL;

trait ContentsNotContainsTrait
{
    public function testOutputNotContains(): void
    {
        fwrite($this->output, 'Another message'.PHP_EOL);

        $this->assertOutputNotContains('Test message');
    }

    public function testOutputNotContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stdout does not contain "Test message".');

        fwrite($this->output, 'Test message'.PHP_EOL);

        $this->assertOutputNotContains('Test message');
    }
}
