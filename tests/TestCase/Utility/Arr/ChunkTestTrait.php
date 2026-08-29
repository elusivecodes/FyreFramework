<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use InvalidArgumentException;

trait ChunkTestTrait
{
    public function testChunkWithEmptyArray(): void
    {
        $this->assertArraysAreIdentical(
            [],
            Arr::chunk([], 2)
        );
    }

    public function testChunkWithInvalidSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Chunk size must be greater than 0.');

        Arr::chunk([], 0);
    }

    public function testChunkWithPreserveKeys(): void
    {
        $this->assertArraysAreIdentical(
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
        $this->assertArraysAreIdentical(
            [
                [1, 2],
                [3, 4],
            ],
            Arr::chunk([1, 2, 3, 4], 2)
        );
    }
}
