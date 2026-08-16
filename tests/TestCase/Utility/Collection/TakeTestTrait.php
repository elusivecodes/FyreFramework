<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait TakeTestTrait
{
    public function testTake(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3],
            Collection::range(1, 10)
                ->take(3)
                ->toArray()
        );
    }

    public function testTakeNegative(): void
    {
        $this->assertArraysAreIdentical(
            [
                7 => 8,
                8 => 9,
                9 => 10,
            ],
            Collection::range(1, 10)
                ->take(-3)
                ->toArray()
        );
    }

    public function testTakeOverflow(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3],
            Collection::range(1, 10)
                ->take(3)
                ->take(4)
                ->toArray()
        );
    }
}
