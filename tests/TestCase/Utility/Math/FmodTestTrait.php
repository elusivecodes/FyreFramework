<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait FmodTestTrait
{
    public function testFmod(): void
    {
        $this->assertSame(
            3.0,
            Math::fmod(33, 5)
        );
    }
}
