<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Closure;
use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait EveryTestTrait
{
    /**
     * @return array<string, array{int[], Closure(int, int): bool, bool}>
     */
    public static function everyProvider(): array
    {
        return [
            'matching' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value <= 10,
                true,
            ],
            'empty' => [
                [],
                static fn(int $value, int $key): bool => false,
                true,
            ],
            'nonmatching' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value <= 5,
                false,
            ],
        ];
    }

    /**
     * @param int[] $values
     * @param Closure(int, int): bool $callback
     */
    #[DataProvider('everyProvider')]
    public function testEvery(array $values, Closure $callback, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::every($values, $callback)
        );
    }
}
