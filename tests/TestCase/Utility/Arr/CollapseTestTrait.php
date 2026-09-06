<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait CollapseTestTrait
{
    /**
     * @return array<string, array{array<int, array<array-key, mixed>>, array<array-key, mixed>}>
     */
    public static function collapseProvider(): array
    {
        return [
            'numeric keys' => [
                [[1, 2], [3, 4]],
                [3, 4],
            ],
            'associative keys' => [
                [['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4]],
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            ],
            'nested arrays' => [
                [['a' => ['b' => 2, 'c' => 3]], ['a' => ['c' => 4, 'd' => 5]]],
                ['a' => ['b' => 2, 'c' => 4, 'd' => 5]],
            ],
            'multiple arrays' => [
                [['a' => 1], ['b' => 2], ['c' => 3]],
                ['a' => 1, 'b' => 2, 'c' => 3],
            ],
        ];
    }

    /**
     * @param array<int, array<array-key, mixed>> $arrays
     * @param array<array-key, mixed> $expected
     */
    #[DataProvider('collapseProvider')]
    public function testCollapse(array $arrays, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Arr::collapse(...$arrays)
        );
    }
}
