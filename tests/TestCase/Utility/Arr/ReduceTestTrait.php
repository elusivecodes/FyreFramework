<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ReduceTestTrait
{
    public function testReduce(): void
    {
        $this->assertSame(
            6,
            Arr::reduce([1, 2, 3], static fn(int $acc, int $value): int => $acc + $value, 0)
        );
    }
}
