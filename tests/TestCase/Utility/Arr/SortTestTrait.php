<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait SortTestTrait
{
    public function testSort(): void
    {
        $this->assertSame(
            ['Item 1', 'Item 10', 'Item 11', 'Item 100', 'Item 101'],
            Arr::sort(['Item 101', 'Item 10', 'Item 1', 'Item 11', 'Item 100'])
        );
    }

    public function testSortCallback(): void
    {
        $this->assertSame(
            [1, 1.25, 1.5, 1.75, 2],
            Arr::sort([1.5, 1.25, 2, 1.75, 1], static fn(float|int $a, float|int $b): int => $a < $b ? -1 : 1)
        );
    }

    public function testSortFlag(): void
    {
        $this->assertSame(
            [1, 1.25, 1.5, 1.75, 2],
            Arr::sort([1.5, 1.25, 2, 1.75, 1], Arr::SORT_NUMERIC)
        );
    }
}
