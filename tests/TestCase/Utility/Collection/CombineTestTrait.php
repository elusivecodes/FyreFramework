<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait CombineTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string, Closure|string}>
     */
    public static function combinePathProvider(): array
    {
        $items = [
            ['id' => 1, 'value' => 'a'],
            ['id' => 2, 'value' => 'b'],
        ];

        return [
            'field path' => [$items, 'id', 'value'],
            'callback' => [
                $items,
                static fn(array $item, int $key): int => $item['id'],
                static fn(array $item, int $key): string => $item['value'],
            ],
            'nested path' => [array_map(static fn(array $item): array => ['data' => $item], $items), 'data.id', 'data.value'],
        ];
    }

    /**
     * @param array<array<string, mixed>> $values
     */
    #[DataProvider('combinePathProvider')]
    public function testCombine(array $values, Closure|string $keyPath, Closure|string $valuePath): void
    {
        $collection = new Collection($values);

        $this->assertArraysAreIdentical(
            [1 => 'a', 2 => 'b'],
            $collection->combine($keyPath, $valuePath)->toArray()
        );
    }

    public function testCombineKeyOnly(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 'a',
            ],
            [
                'id' => 2,
                'value' => 'b',
            ],
        ]);

        $this->assertArraysAreIdentical(
            [
                1 => [
                    'id' => 1,
                    'value' => 'a',
                ],
                2 => [
                    'id' => 2,
                    'value' => 'b',
                ],
            ],
            $collection->combine('id')->toArray()
        );
    }
}
