<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ChunkTestTrait
{
    public function testChunk(): void
    {
        $this->assertSame(
            [[1, 2, 3], [4, 5, 6], [7, 8, 9], [10]],
            Collection::range(1, 10)
                ->chunk(3)
                ->toArray()
        );
    }
}
