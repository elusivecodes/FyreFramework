<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ShuffleTestTrait
{
    public function testShuffleIsRandom(): void
    {
        $array = Arr::range(1, 10);
        $arrays = [];

        for ($i = 0; $i < 1000; $i++) {
            $shuffled = Arr::shuffle($array);
            $sorted = Arr::sort($shuffled, Arr::SORT_NUMERIC);
            $this->assertArraysAreIdentical($array, $sorted);
            $arrays[] = Arr::map($shuffled, static fn(float|int|string $value): string => (string) $value)
                |> Arr::join(...);
        }

        $arrays = Arr::unique($arrays);

        $this->assertGreaterThan(
            100,
            Arr::count($arrays)
        );
    }
}
