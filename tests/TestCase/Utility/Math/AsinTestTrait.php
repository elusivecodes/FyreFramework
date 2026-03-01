<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait AsinTestTrait
{
    public function testAsin(): void
    {
        $this->assertSame(
            .5235987755982989,
            Math::asin(.5)
        );
    }
}
