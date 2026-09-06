<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait DiffTestTrait
{
    /**
     * @return array<string, array{array<int, array<int, int>>|array<int, array<string, int>>, array<int, int>|array<string, int>}>
     */
    public static function diffProvider(): array
    {
        return [
            'value' => [
                [
                    [1, 2, 3, 4, 5],
                    [1, 3, 5],
                ],
                [1 => 2, 3 => 4],
            ],
            'assoc' => [
                [
                    ['a' => 1, 'b' => 2],
                    ['c' => 1, 'd' => 3],
                ],
                ['b' => 2],
            ],
            'nargs' => [
                [
                    [1, 2, 3, 4, 5],
                    [1, 3],
                    [1, 4],
                ],
                [1 => 2, 4 => 5],
            ],
        ];
    }

    /**
     * @param array<int, array<int, int>>|array<int, array<string, int>> $array
     * @param array<int, int>|array<string, int> $expected
     */
    #[DataProvider('diffProvider')]
    public function testDiff(array $array, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Arr::diff(...$array)
        );
    }
}
