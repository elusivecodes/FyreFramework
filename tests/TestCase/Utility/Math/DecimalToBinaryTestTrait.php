<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait DecimalToBinaryTestTrait
{
    public function testDecimalToBinary(): void
    {
        $this->assertSame(
            '101110',
            Math::decimalToBinary(46)
        );
    }
}
