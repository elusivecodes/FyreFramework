<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait FlipTestTrait
{
    public function testFlip(): void
    {
        $this->assertSame(
            [
                1 => 0,
                2 => 1,
                3 => 2,
                4 => 3,
                5 => 4,
                6 => 5,
                7 => 6,
                8 => 7,
                9 => 8,
                10 => 9,
            ],
            Collection::range(1, 10)
                ->flip()
                ->toArray()
        );
    }
}
