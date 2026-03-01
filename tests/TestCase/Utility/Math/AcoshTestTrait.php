<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait AcoshTestTrait
{
    public function testAcosh(): void
    {
        $this->assertSame(
            .9624236501192069,
            Math::acosh(1.5)
        );
    }
}
