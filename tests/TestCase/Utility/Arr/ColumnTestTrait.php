<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait ColumnTestTrait
{
    /**
     * @return array<string, array{array<int, array<string, int>>, array<int, int>}>
     */
    public static function columnProvider(): array
    {
        return [
            'two rows' => [
                [['a' => 1, 'b' => 2], ['a' => 2, 'b' => 3]],
                [1, 2],
            ],
            'missing value' => [
                [['a' => 1, 'b' => 2], ['b' => 3]],
                [1],
            ],
            'three rows' => [
                [['a' => 1, 'b' => 2], ['a' => 2, 'b' => 3], ['a' => 3, 'b' => 4]],
                [1, 2, 3],
            ],
        ];
    }

    /**
     * @param array<int, array<string, int>> $array
     * @param array<int, int> $expected
     */
    #[DataProvider('columnProvider')]
    public function testColumn(array $array, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Arr::column($array, 'a')
        );
    }
}
