<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait LengthTestTrait
{
    public function testLengthWithEmptyString(): void
    {
        $this->assertSame(
            0,
            Str::length('')
        );
    }

    public function testLengthWithString(): void
    {
        $this->assertSame(
            21,
            Str::length('This is a test string')
        );
    }
}
