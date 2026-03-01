<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait EveryTestTrait
{
    public function testEvery(): void
    {
        $this->assertTrue(
            Collection::range(1, 10)
                ->every(static fn(int $value, int $key): bool => $value <= 10)
        );
    }

    public function testEveryEmpty(): void
    {
        $this->assertTrue(
            Collection::empty()
                ->every(static fn(): bool => false)
        );
    }

    public function testEveryFalse(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)
                ->every(static fn(int $value, int $key): bool => $value < 5)
        );
    }
}
