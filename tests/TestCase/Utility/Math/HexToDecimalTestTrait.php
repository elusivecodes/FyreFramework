<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait HexToDecimalTestTrait
{
    public function testHexToDecimal(): void
    {
        $this->assertSame(
            46,
            Math::hexToDecimal('2e')
        );
    }
}
