<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait CountTestTrait
{
    public function testCount(): void
    {
        $this->assertSame(
            2,
            Arr::count(['a', 'b' => ['c']])
        );
    }

    public function testCountRecursive(): void
    {
        $this->assertSame(
            3,
            Arr::count(['a', 'b' => ['c']], Arr::COUNT_RECURSIVE)
        );
    }
}
