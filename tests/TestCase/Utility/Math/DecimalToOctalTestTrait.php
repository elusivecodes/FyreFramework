<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait DecimalToOctalTestTrait
{
    public function testDecimalToOctal(): void
    {
        $this->assertSame(
            '56',
            Math::decimalToOctal(46)
        );
    }
}
