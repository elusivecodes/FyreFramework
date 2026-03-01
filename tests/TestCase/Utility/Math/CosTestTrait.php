<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait CosTestTrait
{
    public function testCos(): void
    {
        $this->assertSame(
            .8775825618903728,
            Math::cos(.5)
        );
    }
}
