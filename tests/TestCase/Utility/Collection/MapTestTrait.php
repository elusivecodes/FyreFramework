<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait MapTestTrait
{
    public function testMap(): void
    {
        $this->assertSame(
            [
                2,
                4,
                6,
                8,
                10,
                12,
                14,
                16,
                18,
                20,
            ],
            Collection::range(1, 10)
                ->map(static fn(int $value, int $key): int => $value * 2)
                ->toArray()
        );
    }
}
