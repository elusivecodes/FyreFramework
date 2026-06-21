<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FindLastKeyTestTrait
{
    public function testFindLastKeyWithMatch(): void
    {
        $this->assertSame(
            1,
            Arr::findLastKey([1, 2, 3, 4, 5], static fn(int $value): bool => $value < 3)
        );
    }

    public function testFindLastKeyWithoutMatch(): void
    {
        /** @var int[] $values */
        $values = [1, 2, 3, 4, 5];

        $this->assertNull(
            Arr::findLastKey($values, static fn(int $value): bool => $value < 1)
        );
    }
}
