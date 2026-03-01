<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait IsNumericTestTrait
{
    public function testIsNumeric(): void
    {
        $this->assertTrue(
            Math::isNumeric('100')
        );
    }

    public function testIsNumericArray(): void
    {
        $this->assertFalse(
            Math::isNumeric([])
        );
    }

    public function testIsNumericBoolean(): void
    {
        $this->assertFalse(
            Math::isNumeric(false)
        );
    }

    public function testIsNumericDecimal(): void
    {
        $this->assertTrue(
            Math::isNumeric('1.5')
        );
    }

    public function testIsNumericFloat(): void
    {
        $this->assertTrue(
            Math::isNumeric(1.5)
        );
    }

    public function testIsNumericInt(): void
    {
        $this->assertTrue(
            Math::isNumeric(100)
        );
    }

    public function testIsNumericNull(): void
    {
        $this->assertFalse(
            Math::isNumeric(null)
        );
    }
}
