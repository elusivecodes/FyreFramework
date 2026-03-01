<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait TanTestTrait
{
    public function testTan(): void
    {
        $this->assertSame(
            .5463024898437905,
            Math::tan(.5)
        );
    }
}
