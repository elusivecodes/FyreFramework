<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use PHPUnit\Framework\AssertionFailedError;

use function fwrite;

use const PHP_EOL;

trait ContentsContainsTrait
{
    public function testErrorContains(): void
    {
        fwrite($this->error, 'Test message'.PHP_EOL);

        $this->assertErrorContains('Test message');
    }

    public function testErrorContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stderr contains "Test message".');

        $this->assertErrorContains('Test message');
    }

    public function testOutputContains(): void
    {
        fwrite($this->output, 'Test message'.PHP_EOL);

        $this->assertOutputContains('Test message');
    }

    public function testOutputContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stdout contains "Test message".');

        $this->assertOutputContains('Test message');
    }
}
