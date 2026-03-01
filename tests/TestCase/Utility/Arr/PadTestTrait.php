<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait PadTestTrait
{
    public function testPad(): void
    {
        $this->assertSame(
            [1, 0, 0],
            Arr::pad([1], 3, 0)
        );
    }
}
