<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait WrapTestTrait
{
    /**
     * @return array<string, array{array<int, int>|int|null, array<int, int>}>
     */
    public static function wrapProvider(): array
    {
        return [
            'scalar' => [1, [1]],
            'array' => [[1], [1]],
            'null' => [null, []],
        ];
    }

    /**
     * @param array<int, int>|int|null $value
     * @param array<int, int> $expected
     */
    #[DataProvider('wrapProvider')]
    public function testWrap(array|int|null $value, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Arr::wrap($value)
        );
    }
}
