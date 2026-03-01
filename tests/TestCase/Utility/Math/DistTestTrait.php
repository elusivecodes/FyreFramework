<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait DistTestTrait
{
    public function testDist(): void
    {
        $this->assertSame(
            1.8027756377319946,
            Math::dist(.5, 1.5, 2, 2.5)
        );
    }
}
