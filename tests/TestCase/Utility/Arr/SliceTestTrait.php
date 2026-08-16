<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait SliceTestTrait
{
    public function testSlice(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3],
            Arr::slice([1, 2, 3])
        );
    }

    public function testSliceWithLength(): void
    {
        $this->assertArraysAreIdentical(
            [3, 4],
            Arr::slice([1, 2, 3, 4, 5], 2, 2)
        );
    }

    public function testSliceWithOffset(): void
    {
        $this->assertArraysAreIdentical(
            [3, 4, 5],
            Arr::slice([1, 2, 3, 4, 5], 2)
        );
    }

    public function testSliceWithPreserveKeys(): void
    {
        $this->assertArraysAreIdentical(
            [2 => 3, 3 => 4],
            Arr::slice([1, 2, 3, 4, 5], 2, 2, true)
        );
    }
}
