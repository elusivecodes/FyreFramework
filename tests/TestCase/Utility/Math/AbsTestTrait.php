<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait AbsTestTrait
{
    public function testAbs(): void
    {
        $this->assertSame(
            1,
            Math::abs(1)
        );
    }

    public function testAbsFloat(): void
    {
        $this->assertSame(
            .5,
            Math::abs(-.5)
        );
    }

    public function testAbsNegative(): void
    {
        $this->assertSame(
            1,
            Math::abs(-1)
        );
    }
}
