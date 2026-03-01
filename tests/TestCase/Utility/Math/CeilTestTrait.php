<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait CeilTestTrait
{
    public function testCeil(): void
    {
        $this->assertSame(
            2.0,
            Math::ceil(1.25)
        );
    }
}
