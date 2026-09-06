<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait IsListTestTrait
{
    /**
     * @return array<string, array{array<array-key, int>, bool}>
     */
    public static function isListProvider(): array
    {
        return [
            'associative keys' => [['a' => 1, 'b' => 2, 'c' => 3], false],
            'empty array' => [[], true],
            'gaps in keys' => [[0 => 1, 2 => 3], false],
            'mixed keys' => [['a' => 1, 2, 'c' => 3], false],
            'negative key' => [[-1 => 0, 0 => 1], false],
            'sequential keys' => [[1, 2, 3], true],
            'out of order keys' => [[1 => 0, 0 => 2], false],
        ];
    }

    /**
     * @param array<array-key, int> $array
     */
    #[DataProvider('isListProvider')]
    public function testIsList(array $array, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::isList($array)
        );
    }
}
