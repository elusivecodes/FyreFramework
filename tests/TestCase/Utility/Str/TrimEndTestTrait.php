<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait TrimEndTestTrait
{
    public function testTrimEndWhitespace(): void
    {
        $this->assertSame(
            "\r\n This is a test string",
            Str::trimEnd("\r\n This is a test string \r\n")
        );
    }

    public function testTrimEndWithMask(): void
    {
        $this->assertSame(
            '000123456',
            Str::trimEnd('000123456000', '0')
        );
    }

    public function testTrimEndWithString(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::trimEnd('This is a test string')
        );
    }
}
