<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Closure;
use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait SomeTestTrait
{
    /**
     * @return array<string, array{int[], Closure(int, int): bool, bool}>
     */
    public static function someProvider(): array
    {
        return [
            'matching' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value === 5,
                true,
            ],
            'empty' => [
                [],
                static fn(int $value, int $key): bool => false,
                false,
            ],
            'nonmatching' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value === 11,
                false,
            ],
        ];
    }

    /**
     * @param int[] $values
     * @param Closure(int, int): bool $callback
     */
    #[DataProvider('someProvider')]
    public function testSome(array $values, Closure $callback, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::some($values, $callback)
        );
    }
}
