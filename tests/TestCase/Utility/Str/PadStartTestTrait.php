<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait PadStartTestTrait
{
    public function testPadStartAboveLength(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::padStart('This is a test string', 10)
        );
    }

    public function testPadStartBelowLength(): void
    {
        $this->assertSame(
            '  This is a test string',
            Str::padStart('This is a test string', 23)
        );
    }

    public function testPadStartWithEmptyString(): void
    {
        $this->assertSame(
            ' ',
            Str::padStart('', 1)
        );
    }

    public function testPadStartWithPadding(): void
    {
        $this->assertSame(
            '__This is a test string',
            Str::padStart('This is a test string', 23, '_')
        );
    }
}
