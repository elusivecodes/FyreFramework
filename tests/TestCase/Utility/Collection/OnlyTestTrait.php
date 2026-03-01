<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait OnlyTestTrait
{
    public function testOnly(): void
    {
        $this->assertSame(
            [
                4 => 5,
                6 => 7,
                8 => 9,
            ],
            Collection::range(1, 10)
                ->only([4, 6, 8, 10])
                ->toArray()
        );
    }
}
