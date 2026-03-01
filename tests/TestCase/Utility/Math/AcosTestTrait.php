<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait AcosTestTrait
{
    public function testAcos(): void
    {
        $this->assertSame(
            1.0471975511965979,
            Math::acos(.5)
        );
    }
}
