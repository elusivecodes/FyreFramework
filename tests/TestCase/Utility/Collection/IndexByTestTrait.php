<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait IndexByTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string, array<mixed>}>
     */
    public static function indexByPathProvider(): array
    {
        $items = [
            ['id' => 1, 'value' => 2],
            ['id' => 2, 'value' => 4],
            ['id' => 3, 'value' => 3],
            ['id' => 4, 'value' => 4],
            ['id' => 5, 'value' => 1],
            ['id' => 6, 'value' => 5],
            ['id' => 7, 'value' => 3],
        ];

        $expected = [
            1 => ['id' => 1, 'value' => 2],
            2 => ['id' => 2, 'value' => 4],
            3 => ['id' => 3, 'value' => 3],
            4 => ['id' => 4, 'value' => 4],
            5 => ['id' => 5, 'value' => 1],
            6 => ['id' => 6, 'value' => 5],
            7 => ['id' => 7, 'value' => 3],
        ];

        return [
            'field path' => [$items, 'id', $expected],
            'callback' => [$items, static fn(array $item, int $key): int => $item['id'], $expected],
            'nested path' => [
                array_map(static fn(array $item): array => ['data' => $item], $items),
                'data.id',
                [
                    1 => [
                        'data' => ['id' => 1, 'value' => 2],
                    ],
                    2 => [
                        'data' => ['id' => 2, 'value' => 4],
                    ],
                    3 => [
                        'data' => ['id' => 3, 'value' => 3],
                    ],
                    4 => [
                        'data' => ['id' => 4, 'value' => 4],
                    ],
                    5 => [
                        'data' => ['id' => 5, 'value' => 1],
                    ],
                    6 => [
                        'data' => ['id' => 6, 'value' => 5],
                    ],
                    7 => [
                        'data' => ['id' => 7, 'value' => 3],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<array<string, mixed>> $values
     * @param array<mixed> $expected
     */
    #[DataProvider('indexByPathProvider')]
    public function testIndexBy(array $values, Closure|string $valuePath, array $expected): void
    {
        $collection = new Collection($values);

        $this->assertArraysAreIdentical(
            $expected,
            $collection->indexBy($valuePath)->toArray()
        );
    }
}
