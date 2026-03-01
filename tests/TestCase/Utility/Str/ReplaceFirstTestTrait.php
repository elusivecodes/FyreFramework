<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ReplaceFirstTestTrait
{
    public function testReplaceFirstWithEmptySearch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replaceFirst('This is a test string', '', 'new')
        );
    }

    public function testReplaceFirstWithMatch(): void
    {
        $this->assertSame(
            'This is a new string',
            Str::replaceFirst('This is a test string', 'test', 'new')
        );
    }

    public function testReplaceFirstWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a new test string',
            Str::replaceFirst('This is a test test string', 'test', 'new')
        );
    }

    public function testReplaceFirstWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replaceFirst('This is a test string', 'invalid', 'new')
        );
    }
}
