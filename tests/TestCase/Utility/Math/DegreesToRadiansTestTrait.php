<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait DegreesToRadiansTestTrait
{
    public function testDegreesToRadians(): void
    {
        $this->assertSame(
            .7853981633974483,
            Math::degreesToRadians(45)
        );
    }
}
