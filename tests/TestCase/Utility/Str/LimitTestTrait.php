<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait LimitTestTrait
{
    public function testLimitExceeded(): void
    {
        $this->assertSame(
            'This is a test string that is designed specifically to contain enough words to go above the default …',
            Str::limit('This is a test string that is designed specifically to contain enough words to go above the default limit of 100 characters.')
        );
    }

    public function testLimitNotReached(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::limit('This is a test string')
        );
    }

    public function testLimitWithAppend(): void
    {
        $this->assertSame(
            'This is a _',
            Str::limit('This is a test string', 10, '_')
        );
    }

    public function testLimitWithLimit(): void
    {
        $this->assertSame(
            'This is a …',
            Str::limit('This is a test string', 10)
        );
    }
}
