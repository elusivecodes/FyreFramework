<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait CoshTestTrait
{
    public function testCosh(): void
    {
        $this->assertSame(
            1.1276259652063807,
            Math::cosh(.5)
        );
    }
}
