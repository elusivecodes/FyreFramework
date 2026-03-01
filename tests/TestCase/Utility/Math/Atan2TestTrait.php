<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait Atan2TestTrait
{
    public function testAtan2(): void
    {
        $this->assertSame(
            .3217505543966422,
            Math::atan2(.5, 1.5)
        );
    }
}
