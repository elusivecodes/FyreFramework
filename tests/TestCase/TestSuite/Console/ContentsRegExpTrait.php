<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use PHPUnit\Framework\AssertionFailedError;

use function fwrite;

use const PHP_EOL;

trait ContentsRegExpTrait
{
    public function testErrorRegExp(): void
    {
        fwrite($this->error, 'Test message 1'.PHP_EOL);

        $this->assertErrorRegExp('/Test message \d+/');
    }

    public function testErrorRegExpFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stderr matches the pattern `/Test message \d+/`.');

        fwrite($this->error, 'Test message A'.PHP_EOL);

        $this->assertErrorRegExp('/Test message \d+/');
    }

    public function testOutputRegExp(): void
    {
        fwrite($this->output, 'Test message 1'.PHP_EOL);

        $this->assertOutputRegExp('/Test message \d+/');
    }

    public function testOutputRegExpFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stdout matches the pattern `/Test message \d+/`.');

        fwrite($this->output, 'Test message A'.PHP_EOL);

        $this->assertOutputRegExp('/Test message \d+/');
    }
}
