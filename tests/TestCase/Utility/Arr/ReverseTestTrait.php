<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ReverseTestTrait
{
    public function testReverse(): void
    {
        $this->assertSame(
            [3, 2, 1],
            Arr::reverse([1, 2, 3])
        );
    }
}
