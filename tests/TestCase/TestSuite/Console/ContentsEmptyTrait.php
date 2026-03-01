<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use PHPUnit\Framework\AssertionFailedError;

use function fwrite;

use const PHP_EOL;

trait ContentsEmptyTrait
{
    public function testErrorEmpty(): void
    {
        $this->assertErrorEmpty();
    }

    public function testErrorEmptyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stderr is empty.');

        fwrite($this->error, 'Test message'.PHP_EOL);

        $this->assertErrorEmpty();
    }

    public function testOutputEmpty(): void
    {
        $this->assertOutputEmpty();
    }

    public function testOutputEmptyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stdout is empty.');

        fwrite($this->output, 'Test message'.PHP_EOL);

        $this->assertOutputEmpty();
    }
}
