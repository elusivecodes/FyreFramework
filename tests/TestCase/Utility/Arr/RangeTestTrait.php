<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait RangeTestTrait
{
    public function testRange(): void
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            Arr::range(1, 5)
        );
    }

    public function testRangeAlpha(): void
    {
        $this->assertSame(
            ['a', 'b', 'c'],
            Arr::range('a', 'c')
        );
    }

    public function testRangeWithStep(): void
    {
        $this->assertSame(
            [1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0],
            Arr::range(1, 5, .5)
        );
    }
}
