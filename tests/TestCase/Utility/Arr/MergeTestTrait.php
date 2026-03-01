<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait MergeTestTrait
{
    public function testMerge(): void
    {
        $this->assertSame(
            [1, 2, 3, 4],
            Arr::merge([1, 2], [3, 4])
        );
    }

    public function testMergeAssoc(): void
    {
        $this->assertSame(
            ['a' => 1, 'b' => 3, 'c' => 4],
            Arr::merge(['a' => 1, 'b' => 2], ['b' => 3, 'c' => 4])
        );
    }

    public function testMergeNArgs(): void
    {
        $this->assertSame(
            [1, 2, 3, 4, 5, 6],
            Arr::merge([1, 2], [3, 4], [5, 6])
        );
    }
}
