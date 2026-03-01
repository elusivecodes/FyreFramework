<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait BeforeTestTrait
{
    public function testBeforeWithEmptySearch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::before('This is a test string', '')
        );
    }

    public function testBeforeWithMatch(): void
    {
        $this->assertSame(
            'This is a',
            Str::before('This is a test string', ' test ')
        );
    }

    public function testBeforeWithMultipleMatches(): void
    {
        $this->assertSame(
            'This is a',
            Str::before('This is a test test string', ' test ')
        );
    }

    public function testBeforeWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::before('This is a test string', 'invalid')
        );
    }
}
