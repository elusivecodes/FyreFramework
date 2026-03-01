<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait RepeatTestTrait
{
    public function testRepeatOneCount(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::repeat('This is a test string', 1)
        );
    }

    public function testRepeatWithCount(): void
    {
        $this->assertSame(
            'This is a test stringThis is a test stringThis is a test string',
            Str::repeat('This is a test string', 3)
        );
    }

    public function testRepeatWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Str::repeat('', 3)
        );
    }

    public function testRepeatZeroCount(): void
    {
        $this->assertSame(
            '',
            Str::repeat('This is a test string', 0)
        );
    }
}
