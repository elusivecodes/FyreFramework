<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FindTestTrait
{
    public function testFindWithDefault(): void
    {
        $this->assertSame(
            6,
            Arr::find([1, 2, 3, 4, 5], static fn(int $value): bool => $value > 5, 6),
        );
    }

    public function testFindWithMatch(): void
    {
        $this->assertSame(
            3,
            Arr::find([1, 2, 3, 4, 5], static fn(int $value): bool => $value > 2)
        );
    }

    public function testFindWithoutMatch(): void
    {
        $this->assertNull(
            Arr::find([1, 2, 3, 4, 5], static fn(int $value): bool => $value > 5)
        );
    }
}
