<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ContainsAllTestTrait
{
    public function testContainsAllWithEmptyString(): void
    {
        $this->assertTrue(
            Str::containsAll('This is a test string', [''])
        );
    }

    public function testContainsAllWithMatches(): void
    {
        $this->assertTrue(
            Str::containsAll('This is a string', ['string', 'is'])
        );
    }

    public function testContainsAllWithoutMatch(): void
    {
        $this->assertFalse(
            Str::containsAll('This is a string', ['test', 'value'])
        );
    }

    public function testContainsAllWithSingleMatch(): void
    {
        $this->assertFalse(
            Str::containsAll('This is a string', ['is', 'value'])
        );
    }
}
