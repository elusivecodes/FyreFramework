<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait FindLastTestTrait
{
    public function testFindLast(): void
    {
        $this->assertSame(
            5,
            Collection::range(1, 10)
                ->findLast(static fn(int $value, int $key): bool => $value < 6)
        );
    }

    public function testFindLastInvalid(): void
    {
        $this->assertNull(
            Collection::range(1, 10)
                ->findLast(static fn(int $value, int $key): bool => $value < 0)
        );
    }
}
