<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait SkipWhileTestTrait
{
    public function testSkipWhile(): void
    {
        $this->assertSame(
            [
                3 => 4,
                4 => 5,
                5 => 6,
                6 => 7,
                7 => 8,
                8 => 9,
                9 => 10,
            ],
            Collection::range(1, 10)
                ->skipWhile(static fn(int $value, int $key): bool => $value <= 3)
                ->toArray()
        );
    }
}
