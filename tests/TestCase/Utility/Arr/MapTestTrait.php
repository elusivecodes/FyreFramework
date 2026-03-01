<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait MapTestTrait
{
    public function testMap(): void
    {
        $this->assertSame(
            [2, 4, 6],
            Arr::map([1, 2, 3], static fn(int $value): int => $value * 2)
        );
    }

    public function testMapWithKey(): void
    {
        $this->assertSame(
            [1, 4, 6],
            Arr::map([1, 2, 3], static fn(int $value, int $key): int => $key > 0 ? $value * 2 : $value)
        );
    }
}
