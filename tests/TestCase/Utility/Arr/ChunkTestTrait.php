<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ChunkTestTrait
{
    public function testChunkWithEmptyArray(): void
    {
        $this->assertSame(
            [],
            Arr::chunk([], 2)
        );
    }

    public function testChunkWithPreserveKeys(): void
    {
        $this->assertSame(
            [
                [
                    0 => 1,
                    1 => 2,
                ],
                [
                    2 => 3,
                    3 => 4,
                ],
            ],
            Arr::chunk([1, 2, 3, 4], 2, true)
        );
    }

    public function testChunkWithSize(): void
    {
        $this->assertSame(
            [
                [1, 2],
                [3, 4],
            ],
            Arr::chunk([1, 2, 3, 4], 2)
        );
    }
}
