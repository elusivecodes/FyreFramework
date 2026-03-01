<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ReduceTestTrait
{
    public function testReduceArray(): void
    {
        $this->assertSame(
            55,
            Collection::range(1, 10)->reduce(static fn(int $acc, int $item, int $key): int => $acc + $item, 0)
        );
    }
}
