<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use PHPUnit\Framework\AssertionFailedError;

use function fwrite;

use const PHP_EOL;

trait ContentsContainsRowTrait
{
    public function testOutputContainsRow(): void
    {
        fwrite($this->output, '| a | b | c  |'.PHP_EOL);

        $this->assertOutputContainsRow(['a', 'b', 'c']);
    }

    public function testOutputContainsRowFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that stdout contains the row "a,b,c".');

        $this->assertOutputContainsRow(['a', 'b', 'c']);
    }
}
