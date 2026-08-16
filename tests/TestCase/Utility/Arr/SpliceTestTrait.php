<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait SpliceTestTrait
{
    public function testSplice(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $this->assertArraysAreIdentical(
            [3, 4, 5, 6],
            Arr::splice($array, 2)
        );
        $this->assertArraysAreIdentical(
            [1, 2],
            $array
        );
    }

    public function testSpliceNegativeOffset(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $this->assertArraysAreIdentical(
            [5, 6],
            Arr::splice($array, -2)
        );
        $this->assertArraysAreIdentical(
            [1, 2, 3, 4],
            $array
        );
    }

    public function testSpliceWithLength(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $this->assertArraysAreIdentical(
            [3],
            Arr::splice($array, 2, 1)
        );
        $this->assertArraysAreIdentical(
            [1, 2, 4, 5, 6],
            $array
        );
    }

    public function testSpliceWithNegativeLength(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $this->assertArraysAreIdentical(
            [3, 4, 5],
            Arr::splice($array, 2, -1)
        );
        $this->assertArraysAreIdentical(
            [1, 2, 6],
            $array
        );
    }

    public function testSpliceWithReplacement(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $this->assertArraysAreIdentical(
            [3],
            Arr::splice($array, 2, 1, 0)
        );
        $this->assertArraysAreIdentical(
            [1, 2, 0, 4, 5, 6],
            $array
        );
    }
}
