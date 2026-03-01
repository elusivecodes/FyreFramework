<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ReplaceEachTestTrait
{
    public function testReplaceEachWithMatches(): void
    {
        $this->assertSame(
            'This is a new phrase',
            Str::replaceEach('This is a test string', [
                'test' => 'new',
                'string' => 'phrase',
            ])
        );
    }

    public function testReplaceEachWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a new new phrase',
            Str::replaceEach('This is a test test string', [
                'test' => 'new',
                'string' => 'phrase',
            ])
        );
    }

    public function testReplaceEachWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::replaceEach('This is a test string', [
                'invalid' => 'new',
                'sentence' => 'phrase',
            ])
        );
    }
}
