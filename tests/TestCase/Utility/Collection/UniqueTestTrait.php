<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait UniqueTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string, array<mixed>}>
     */
    public static function uniquePathProvider(): array
    {
        $items = [
            ['value' => 1],
            ['value' => 2],
            ['value' => 3],
            ['value' => 3],
            ['value' => 4],
            ['value' => 5],
        ];

        $expected = [
            0 => ['value' => 1],
            1 => ['value' => 2],
            2 => ['value' => 3],
            4 => ['value' => 4],
            5 => ['value' => 5],
        ];

        return [
            'field path' => [$items, 'value', $expected],
            'callback' => [$items, static fn(array $item, int $key): int => $item['value'], $expected],
            'nested path' => [
                array_map(static fn(array $item): array => ['data' => $item], $items),
                'data.value',
                [
                    0 => [
                        'data' => ['value' => 1],
                    ],
                    1 => [
                        'data' => ['value' => 2],
                    ],
                    2 => [
                        'data' => ['value' => 3],
                    ],
                    4 => [
                        'data' => ['value' => 4],
                    ],
                    5 => [
                        'data' => ['value' => 5],
                    ],
                ],
            ],
        ];
    }

    public function testUnique(): void
    {
        $collection = new Collection([
            1,
            2,
            3,
            3,
            4,
            5,
        ]);

        $this->assertArraysAreIdentical(
            [
                0 => 1,
                1 => 2,
                2 => 3,
                4 => 4,
                5 => 5,
            ],
            $collection
                ->unique()
                ->toArray()
        );
    }

    /**
     * @param array<array<string, mixed>> $values
     * @param array<mixed> $expected
     */
    #[DataProvider('uniquePathProvider')]
    public function testUniquePath(array $values, Closure|string $valuePath, array $expected): void
    {
        $collection = new Collection($values);

        $this->assertArraysAreIdentical(
            $expected,
            $collection->unique($valuePath)->toArray()
        );
    }
}
