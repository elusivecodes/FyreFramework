<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait MergeTestTrait
{
    /**
     * @return array<string, array{array<int, array<int, int>>|array<int, array<string, int>>, array<int, int>|array<string, int>}>
     */
    public static function mergeProvider(): array
    {
        return [
            'value' => [
                [
                    [1, 2],
                    [3, 4],
                ],
                [1, 2, 3, 4],
            ],
            'assoc' => [
                [
                    ['a' => 1, 'b' => 2],
                    ['b' => 3, 'c' => 4],
                ],
                ['a' => 1, 'b' => 3, 'c' => 4],
            ],
            'nargs' => [
                [
                    [1, 2],
                    [3, 4],
                    [5, 6],
                ],
                [1, 2, 3, 4, 5, 6],
            ],
        ];
    }

    /**
     * @param array<int, array<int, int>>|array<int, array<string, int>> $arrays
     * @param array<int, int>|array<string, int> $expected
     */
    #[DataProvider('mergeProvider')]
    public function testMerge(array $arrays, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Arr::merge(...$arrays)
        );
    }
}
