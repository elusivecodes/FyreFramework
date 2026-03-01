<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait DecimalToHexTestTrait
{
    public function testDecimalToHex(): void
    {
        $this->assertSame(
            '2e',
            Math::decimalToHex(46)
        );
    }
}
