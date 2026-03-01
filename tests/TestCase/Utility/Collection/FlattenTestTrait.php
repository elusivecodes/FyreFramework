<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait FlattenTestTrait
{
    public function testFlatten(): void
    {
        $collection = new Collection([1, [2, 3], [[4, 5]]]);

        $this->assertSame(
            [1, 2, 3, 4, 5],
            $collection->flatten()->toArray()
        );
    }

    public function testFlattenMaxDepth(): void
    {
        $collection = new Collection([1, [2, 3], [[4, 5]]]);

        $this->assertSame(
            [1, 2, 3, [4, 5]],
            $collection->flatten(1)->toArray()
        );
    }
}
