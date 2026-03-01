<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait FloorTestTrait
{
    public function testFloor(): void
    {
        $this->assertSame(
            2.0,
            Math::floor(2.75)
        );
    }
}
