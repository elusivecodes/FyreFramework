<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use InvalidArgumentException;

trait CountTestTrait
{
    public function testCount(): void
    {
        $this->assertSame(
            2,
            Arr::count(['a', 'b' => ['c']])
        );
    }

    public function testCountInvalidMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Count mode must be COUNT_NORMAL or COUNT_RECURSIVE.');

        Arr::count([], 2);
    }

    public function testCountRecursive(): void
    {
        $this->assertSame(
            3,
            Arr::count(['a', 'b' => ['c']], Arr::COUNT_RECURSIVE)
        );
    }
}
