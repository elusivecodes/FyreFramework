<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FindLastTestTrait
{
    public function testFindLastWithDefault(): void
    {
        $this->assertSame(
            0,
            Arr::findLast([1, 2, 3, 4, 5], static fn(int $value): bool => $value < 1, 0),
        );
    }

    public function testFindLastWithMatch(): void
    {
        $this->assertSame(
            2,
            Arr::findLast([1, 2, 3, 4, 5], static fn(int $value): bool => $value < 3)
        );
    }

    public function testFindLastWithoutMatch(): void
    {
        $this->assertNull(
            Arr::findLast([1, 2, 3, 4, 5], static fn(int $value): bool => $value < 1)
        );
    }
}
