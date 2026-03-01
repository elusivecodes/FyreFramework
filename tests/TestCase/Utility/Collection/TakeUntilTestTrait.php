<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait TakeUntilTestTrait
{
    public function testTakeUntil(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Collection::range(1, 10)
                ->takeUntil(static fn(int $value, int $key): bool => $value > 3)
                ->toArray()
        );
    }
}
