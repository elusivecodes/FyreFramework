<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ExceptTestTrait
{
    public function testExcept(): void
    {
        $this->assertSame(
            [
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 4,
                5 => 6,
                7 => 8,
                9 => 10,

            ],
            Collection::range(1, 10)
                ->except([4, 6, 8, 10])
                ->toArray()
        );
    }
}
