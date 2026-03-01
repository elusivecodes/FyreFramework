<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait OctalToDecimalTestTrait
{
    public function testOctalToDecimal(): void
    {
        $this->assertSame(
            46,
            Math::octalToDecimal('56')
        );
    }
}
