<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait Log10TestTrait
{
    public function testLog10(): void
    {
        $this->assertSame(
            1.3979400086720377,
            Math::log10(25)
        );
    }
}
