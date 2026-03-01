<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait LogPlus1TestTrait
{
    public function testLogPlus1(): void
    {
        $this->assertSame(
            3.258096538021482,
            Math::logPlus1(25)
        );
    }
}
