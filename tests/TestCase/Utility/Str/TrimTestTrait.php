<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait TrimTestTrait
{
    public function testTrimWhitespace(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::trim("\r\n This is a test string \r\n")
        );
    }

    public function testTrimWithMask(): void
    {
        $this->assertSame(
            '123456',
            Str::trim('000123456000', '0')
        );
    }

    public function testTrimWithString(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::trim('This is a test string')
        );
    }
}
