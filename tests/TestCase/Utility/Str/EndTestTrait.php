<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait EndTestTrait
{
    public function testEndWithEmptyString(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::end('This is a test string', '')
        );
    }

    public function testEndWithMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::end('This is a test string', ' a test string')
        );
    }

    public function testEndWithoutMatch(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::end('This is a ', 'test string')
        );
    }
}
