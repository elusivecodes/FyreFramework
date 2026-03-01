<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait TakeWhileTestTrait
{
    public function testTakeWhile(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Collection::range(1, 10)
                ->takeWhile(static fn(int $value, int $key): bool => $value <= 3)
                ->toArray()
        );
    }
}
