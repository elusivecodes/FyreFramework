<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use InvalidArgumentException;

trait FlattenTestTrait
{
    public function testFlatten(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3, 4],
            Arr::flatten([1, 2, [3, 4]])
        );
    }

    public function testFlattenDeep(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3, [4, 5]],
            Arr::flatten([1, 2, [3, [4, 5]]])
        );
    }

    public function testFlattenInvalidDepth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Maximum depth must be greater than 0.');

        Arr::flatten([], 0);
    }

    public function testFlattenWithDepth(): void
    {
        $this->assertArraysAreIdentical(
            [1, 2, 3, 4, 5],
            Arr::flatten([1, 2, [3, [4, 5]]], 2)
        );
    }
}
