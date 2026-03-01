<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait FilterTestTrait
{
    public function testFilter(): void
    {
        $this->assertSame(
            [
                1 => 2,
                3 => 4,
                5 => 6,
                7 => 8,
                9 => 10,
            ],
            Collection::range(1, 10)
                ->filter(static fn(int $value, int $key): bool => $value % 2 === 0)
                ->toArray()
        );
    }
}
