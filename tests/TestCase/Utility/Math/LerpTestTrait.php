<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait LerpTestTrait
{
    public function testLerp(): void
    {
        $this->assertSame(
            75.0,
            Math::lerp(50, 100, .5)
        );
    }
}
