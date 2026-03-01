<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ReplaceTestTrait
{
    public function testReplaceWithEmptySearch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replace('This is a test string', '', 'new')
        );
    }

    public function testReplaceWithMatch(): void
    {
        $this->assertSame(
            'This is a new string',
            Str::replace('This is a test string', 'test', 'new')
        );
    }

    public function testReplaceWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a new new string',
            Str::replace('This is a test test string', 'test', 'new')
        );
    }

    public function testReplaceWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replace('This is a test string', 'invalid', 'new')
        );
    }
}
