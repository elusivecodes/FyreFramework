<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FilterTestTrait
{
    public function testFilter(): void
    {
        $this->assertSame(
            [1 => 1, 3 => 2, 4 => 3],
            Arr::filter([0, 1, '', 2, 3])
        );
    }

    public function testFilterWithCallback(): void
    {
        $this->assertSame(
            [2 => 3, 3 => 4, 4 => 5],
            Arr::filter([1, 2, 3, 4, 5], static fn(int $value): bool => $value > 2)
        );
    }

    public function testFilterWithMode(): void
    {
        $this->assertSame(
            [3 => 4, 4 => 5],
            Arr::filter([1, 2, 3, 4, 5], static fn(int $key): bool => $key > 2, Arr::FILTER_KEY)
        );
    }
}
