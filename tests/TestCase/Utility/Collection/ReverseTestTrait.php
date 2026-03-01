<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ReverseTestTrait
{
    public function testReverse(): void
    {
        $this->assertSame(
            [
                9 => 10,
                8 => 9,
                7 => 8,
                6 => 7,
                5 => 6,
                4 => 5,
                3 => 4,
                2 => 3,
                1 => 2,
                0 => 1,
            ],
            Collection::range(1, 10)
                ->reverse()
                ->toArray()
        );
    }
}
