<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait BeforeLastTestTrait
{
    public function testBeforeLastWithEmptySearch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::beforeLast('This is a test string', '')
        );
    }

    public function testBeforeLastWithMatch(): void
    {
        $this->assertSame(
            'This is a',
            Str::beforeLast('This is a test string', ' test ')
        );
    }

    public function testBeforeLastWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a test',
            Str::beforeLast('This is a test test string', ' test ')
        );
    }

    public function testBeforeLastWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::beforeLast('This is a test string', 'invalid')
        );
    }
}
