<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FindKeyTestTrait
{
    public function testFindKeyWithMatch(): void
    {
        $this->assertSame(
            2,
            Arr::findKey([1, 2, 3, 4, 5], static fn(int $value): bool => $value > 2)
        );
    }

    public function testFindKeyWithoutMatch(): void
    {
        /** @var int[] $values */
        $values = [1, 2, 3, 4, 5];

        $this->assertNull(
            Arr::findKey($values, static fn(int $value): bool => $value > 5)
        );
    }
}
