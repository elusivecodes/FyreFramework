<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait PadEndTestTrait
{
    public function testPadEndAboveLength(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::padEnd('This is a test string', 10)
        );
    }

    public function testPadEndBelowLength(): void
    {
        $this->assertSame(
            'This is a test string  ',
            Str::padEnd('This is a test string', 23)
        );
    }

    public function testPadEndWithEmptyString(): void
    {
        $this->assertSame(
            ' ',
            Str::padEnd('', 1)
        );
    }

    public function testPadEndWithPadding(): void
    {
        $this->assertSame(
            'This is a test string__',
            Str::padEnd('This is a test string', 23, '_')
        );
    }
}
