<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait EndsWithTestTrait
{
    public function testEndsWithWithEmptyString(): void
    {
        $this->assertFalse(
            Str::endsWith('This is a test string', '')
        );
    }

    public function testEndsWithWithMatch(): void
    {
        $this->assertTrue(
            Str::endsWith('This is a test string', ' a test string')
        );
    }

    public function testEndsWithWithoutMatch(): void
    {
        $this->assertFalse(
            Str::endsWith('This is a ', 'test string')
        );
    }
}
