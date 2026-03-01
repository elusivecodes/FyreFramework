<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ContainsTestTrait
{
    public function testContainsWithEmptyString(): void
    {
        $this->assertTrue(
            Str::contains('This is a test string', '')
        );
    }

    public function testContainsWithMatch(): void
    {
        $this->assertTrue(
            Str::contains('This is a test string', 'test')
        );
    }

    public function testContainsWithMatchAtStart(): void
    {
        $this->assertTrue(
            Str::contains('This is a test string', 'This')
        );
    }

    public function testContainsWithoutMatch(): void
    {
        $this->assertFalse(
            Str::contains('This is a string', 'test')
        );
    }
}
