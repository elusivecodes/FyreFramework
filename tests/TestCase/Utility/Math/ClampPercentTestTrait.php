<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait ClampPercentTestTrait
{
    public function testClampPercent(): void
    {
        $this->assertSame(
            50,
            Math::clampPercent(50)
        );
    }

    public function testClampPercentAbove(): void
    {
        $this->assertSame(
            100,
            Math::clampPercent(150)
        );
    }

    public function testClampPercentBelow(): void
    {
        $this->assertSame(
            0,
            Math::clampPercent(-50)
        );
    }
}
