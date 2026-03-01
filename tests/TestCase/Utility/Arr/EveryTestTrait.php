<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait EveryTestTrait
{
    public function testEvery(): void
    {
        $this->assertTrue(
            Arr::every(
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value <= 10
            )
        );
    }

    public function testEveryEmpty(): void
    {
        $this->assertTrue(
            Arr::every(
                [],
                static fn(int $value, int $key): bool => false
            )
        );
    }

    public function testEveryFalse(): void
    {
        $this->assertFalse(
            Arr::every(
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value <= 5
            )
        );
    }
}
