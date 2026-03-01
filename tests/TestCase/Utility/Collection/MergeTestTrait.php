<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait MergeTestTrait
{
    public function testMergeArray(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertSame(
            [1, 2, 3, 4, 5, 6],
            $collection->merge([4, 5, 6])->toArray()
        );
    }

    public function testMergeCollection(): void
    {
        $collection1 = new Collection([1, 2, 3]);
        $collection2 = new Collection([4, 5, 6]);

        $this->assertSame(
            [1, 2, 3, 4, 5, 6],
            $collection1->merge($collection2)->toArray()
        );
    }

    public function testMergeMultiple(): void
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertSame(
            [1, 2, 3, 4, 5, 6, 7, 8, 9],
            $collection->merge([4, 5, 6], [7, 8, 9])->toArray()
        );
    }
}
