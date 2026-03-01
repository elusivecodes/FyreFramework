<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FlattenTestTrait
{
    public function testFlatten(): void
    {
        $this->assertSame(
            [1, 2, 3, 4],
            Arr::flatten([1, 2, [3, 4]])
        );
    }

    public function testFlattenDeep(): void
    {
        $this->assertSame(
            [1, 2, 3, [4, 5]],
            Arr::flatten([1, 2, [3, [4, 5]]])
        );
    }

    public function testFlattenWithDepth(): void
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            Arr::flatten([1, 2, [3, [4, 5]]], 2)
        );
    }
}
