<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait ClampTestTrait
{
    public function testClamp(): void
    {
        $this->assertSame(
            .5,
            Math::clamp(.5)
        );
    }

    public function testClampAbove(): void
    {
        $this->assertSame(
            2,
            Math::clamp(3, 1, 2)
        );
    }

    public function testClampBelow(): void
    {
        $this->assertSame(
            1,
            Math::clamp(0, 1, 2)
        );
    }
}
