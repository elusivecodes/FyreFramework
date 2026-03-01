<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ReplaceLastTestTrait
{
    public function testReplaceLastWithEmptySearch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replaceLast('This is a test string', '', 'new')
        );
    }

    public function testReplaceLastWithMatch(): void
    {
        $this->assertSame(
            'This is a new string',
            Str::replaceLast('This is a test string', 'test', 'new')
        );
    }

    public function testReplaceLastWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a test new string',
            Str::replaceLast('This is a test test string', 'test', 'new')
        );
    }

    public function testReplaceLastWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replaceLast('This is a test string', 'invalid', 'new')
        );
    }
}
