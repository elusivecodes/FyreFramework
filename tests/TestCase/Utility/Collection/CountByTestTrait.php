<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait CountByTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string}>
     */
    public static function countByPathProvider(): array
    {
        $items = [
            ['id' => 1, 'value' => 2],
            ['id' => 2, 'value' => 4],
            ['id' => 3, 'value' => 3],
            ['id' => 4, 'value' => 4],
            ['id' => 5, 'value' => 4],
            ['id' => 6, 'value' => 2],
        ];

        return [
            'field path' => [$items, 'value'],
            'callback' => [$items, static fn(array $item, int $key): int => $item['value']],
            'nested path' => [array_map(static fn(array $item): array => ['data' => $item], $items), 'data.value'],
        ];
    }

    /**
     * @param array<array<string, mixed>> $values
     */
    #[DataProvider('countByPathProvider')]
    public function testCountBy(array $values, Closure|string $valuePath): void
    {
        $collection = new Collection($values);

        $this->assertArraysAreIdentical(
            [2 => 2, 4 => 3, 3 => 1],
            $collection->countBy($valuePath)->toArray()
        );
    }
}
