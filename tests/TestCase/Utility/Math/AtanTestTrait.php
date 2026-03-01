<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait AtanTestTrait
{
    public function testAtan(): void
    {
        $this->assertSame(
            .4636476090008061,
            Math::atan(.5)
        );
    }
}
