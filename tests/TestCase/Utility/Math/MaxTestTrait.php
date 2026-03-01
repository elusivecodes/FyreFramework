<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait MaxTestTrait
{
    public function testMax(): void
    {
        $this->assertSame(
            150,
            Math::max(25, 10, 50, 25, 150)
        );
    }
}
