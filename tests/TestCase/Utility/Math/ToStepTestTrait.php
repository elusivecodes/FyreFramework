<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait ToStepTestTrait
{
    public function testToStep(): void
    {
        $this->assertSame(
            .1,
            Math::toStep(.12345, .1)
        );
    }

    public function testToStepInt(): void
    {
        $this->assertSame(
            132.0,
            Math::toStep(123, 33)
        );
    }
}
