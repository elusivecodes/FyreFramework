<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait DiffTestTrait
{
    public function testDiff(): void
    {
        $this->assertArraysAreIdentical(
            [1 => 2, 3 => 4],
            Arr::diff([1, 2, 3, 4, 5], [1, 3, 5])
        );
    }

    public function testDiffAssoc(): void
    {
        $this->assertArraysAreIdentical(
            ['b' => 2],
            Arr::diff(['a' => 1, 'b' => 2], ['c' => 1, 'd' => 3])
        );
    }

    public function testDiffNArgs(): void
    {
        $this->assertArraysAreIdentical(
            [1 => 2, 4 => 5],
            Arr::diff([1, 2, 3, 4, 5], [1, 3], [1, 4])
        );
    }
}
