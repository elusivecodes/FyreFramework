<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait SliceTestTrait
{
    public function testSliceWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Str::slice('', 10)
        );
    }

    public function testSliceWithNegativeLength(): void
    {
        $this->assertSame(
            'test',
            Str::slice('This is a test string', 10, -7)
        );
    }

    public function testSliceWithNegativeStart(): void
    {
        $this->assertSame(
            'test string',
            Str::slice('This is a test string', -11)
        );
    }

    public function testSliceWithOutOfBoundsStart(): void
    {
        $this->assertSame(
            '',
            Str::slice('This is a test string', 21)
        );
    }

    public function testSliceWithPositiveLength(): void
    {
        $this->assertSame(
            'test',
            Str::slice('This is a test string', 10, 4)
        );
    }

    public function testSliceWithPositiveStart(): void
    {
        $this->assertSame(
            'test string',
            Str::slice('This is a test string', 10)
        );
    }
}
