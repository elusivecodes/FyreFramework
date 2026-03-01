<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait SkipTestTrait
{
    public function testSkip(): void
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
                ->skip(3)
                ->toArray()
        );
    }

    public function testSkipOverflow(): void
    {
        $this->assertSame(
            [],
            Collection::range(1, 10)
                ->skip(11)
                ->toArray()
        );
    }
}
