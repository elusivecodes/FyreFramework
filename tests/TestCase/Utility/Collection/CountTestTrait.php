<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;
use Generator;

trait CountTestTrait
{
    public function testCountArray(): void
    {
        $collection = new Collection([1, 2, 3, 4]);

        $this->assertSame(
            4,
            $collection->count()
        );
    }

    public function testCountGenerator(): void
    {
        $collection = new Collection(static function(): Generator {
            yield 1;
            yield 2;
            yield 3;
            yield 4;
        });

        $this->assertSame(
            4,
            $collection->count()
        );
    }
}
