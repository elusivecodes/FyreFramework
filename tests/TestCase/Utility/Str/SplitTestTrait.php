<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait SplitTestTrait
{
    public function testSplitWithEmptyString(): void
    {
        $this->assertSame(
            [''],
            Str::split('', ' ')
        );
    }

    public function testSplitWithNegativeLimit(): void
    {
        $this->assertSame(
            [
                'This',
                'is',
                'a',
                'test',
            ],
            Str::split('This is a test string', ' ', -1)
        );
    }

    public function testSplitWithPositiveLimit(): void
    {
        $this->assertSame(
            [
                'This',
                'is',
                'a test string',
            ],
            Str::split('This is a test string', ' ', 3)
        );
    }

    public function testSplitWithSpace(): void
    {
        $this->assertSame(
            [
                'This',
                'is',
                'a',
                'test',
                'string',
            ],
            Str::split('This is a test string', ' ')
        );
    }

    public function testSplitWithString(): void
    {
        $this->assertSame(
            [
                'This is a',
                'string',
            ],
            Str::split('This is a test string', ' test ')
        );
    }

    public function testSplitWithZeroLimit(): void
    {
        $this->assertSame(
            [
                'This is a test string',
            ],
            Str::split('This is a test string', ' ', 0)
        );
    }
}
