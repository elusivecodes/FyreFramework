<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait IntersectTestTrait
{
    public function testIntersect(): void
    {
        $this->assertSame(
            [0 => 1, 2 => 3, 4 => 5],
            Arr::intersect([1, 2, 3, 4, 5], [1, 3, 5])
        );
    }

    public function testIntersectNArgs(): void
    {
        $this->assertSame(
            [0 => 1, 2 => 3],
            Arr::intersect([1, 2, 3, 4, 5], [1, 3, 5], [1, 3, 4])
        );
    }
}
