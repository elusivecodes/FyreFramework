<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait ExpMinus1TestTrait
{
    public function testExpMinus1(): void
    {
        $this->assertSame(
            .6487212707001282,
            Math::expMinus1(.5)
        );
    }
}
