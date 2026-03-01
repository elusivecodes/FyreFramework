<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait SqrtTestTrait
{
    public function testSqrt(): void
    {
        $this->assertSame(
            1.4142135623730951,
            Math::sqrt(2)
        );
    }
}
