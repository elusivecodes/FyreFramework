<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait HypotTestTrait
{
    public function testHypot(): void
    {
        $this->assertSame(
            2.5495097567963922,
            Math::hypot(.5, 2.5)
        );
    }
}
