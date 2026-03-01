<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait TanhTestTrait
{
    public function testTanh(): void
    {
        $this->assertSame(
            .46211715726000974,
            Math::tanh(.5)
        );
    }
}
