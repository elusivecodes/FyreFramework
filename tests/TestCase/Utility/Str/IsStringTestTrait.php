<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait IsStringTestTrait
{
    public function testIsString(): void
    {
        $this->assertTrue(
            Str::isString('This is a test string')
        );
    }

    public function testIsStringArray(): void
    {
        $this->assertFalse(
            Str::isString([1, 2, 3])
        );
    }

    public function testIsStringBoolean(): void
    {
        $this->assertFalse(
            Str::isString(true)
        );
    }

    public function testIsStringFloat(): void
    {
        $this->assertFalse(
            Str::isString(123.456)
        );
    }

    public function testIsStringInt(): void
    {
        $this->assertFalse(
            Str::isString(123)
        );
    }

    public function testIsStringNull(): void
    {
        $this->assertFalse(
            Str::isString(null)
        );
    }
}
