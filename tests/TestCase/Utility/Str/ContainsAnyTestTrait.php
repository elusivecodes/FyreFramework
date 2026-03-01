<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ContainsAnyTestTrait
{
    public function testContainsAnyWithEmptyString(): void
    {
        $this->assertTrue(
            Str::containsAny('This is a test string', [''])
        );
    }

    public function testContainsAnyWithMatch(): void
    {
        $this->assertTrue(
            Str::containsAny('This is a string', ['test', 'is'])
        );
    }

    public function testContainsAnyWithMatchAtStart(): void
    {
        $this->assertTrue(
            Str::containsAny('This is a test string', ['This'])
        );
    }

    public function testContainsAnyWithoutMatch(): void
    {
        $this->assertFalse(
            Str::containsAny('This is a string', ['test', 'value'])
        );
    }
}
