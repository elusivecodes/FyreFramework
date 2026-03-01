<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait LastIndexOfTestTrait
{
    public function testLastIndexOfWithEmptySearch(): void
    {
        $this->assertSame(
            21,
            Str::lastIndexOf('This is a test string', '')
        );
    }

    public function testLastIndexOfWithMatch(): void
    {
        $this->assertSame(
            9,
            Str::lastIndexOf('This is a test string', ' test ')
        );
    }

    public function testLastIndexOfWithMultipleMatches(): void
    {
        $this->assertSame(
            14,
            Str::lastIndexOf('This is a test test string', ' test ')
        );
    }

    public function testLastIndexOfWithNegativeStart(): void
    {
        $this->assertSame(
            9,
            Str::lastIndexOf('This is a test test string', ' test ', -13)
        );
    }

    public function testLastIndexOfWithoutMatch(): void
    {
        $this->assertSame(
            -1,
            Str::lastIndexOf('This is a test string', 'invalid')
        );
    }

    public function testLastIndexOfWithPositiveStart(): void
    {
        $this->assertSame(
            14,
            Str::lastIndexOf('This is a test test string', ' test ', 10)
        );
    }
}
