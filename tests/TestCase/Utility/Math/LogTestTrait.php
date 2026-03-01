<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait LogTestTrait
{
    public function testLog(): void
    {
        $this->assertSame(
            3.2188758248682006,
            Math::log(25)
        );
    }

    public function testLogWithBase(): void
    {
        $this->assertSame(
            4.643856189774724,
            Math::log(25, 2)
        );
    }
}
