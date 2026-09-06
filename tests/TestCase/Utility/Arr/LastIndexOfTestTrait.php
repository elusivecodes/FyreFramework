<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait LastIndexOfTestTrait
{
    /**
     * @return array<string, array{array<array-key, int|string>, string, false|int|string}>
     */
    public static function lastIndexOfProvider(): array
    {
        return [
            'numeric keys' => [['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'c', 5],
            'associative keys' => [['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 3, 'f' => 3, 'g' => 5], '3', 'f'],
            'without match' => [['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'z', false],
        ];
    }

    /**
     * @param array<array-key, int|string> $array
     */
    #[DataProvider('lastIndexOfProvider')]
    public function testLastIndexOf(array $array, string $value, false|int|string $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::lastIndexOf($array, $value)
        );
    }

    public function testLastIndexOfWithStrict(): void
    {
        $this->assertSame(
            5,
            Arr::lastIndexOf([1, 2, '1', 3, '1', '1', 4, 1], '1', true)
        );
    }
}
