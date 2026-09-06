<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait MedianTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string}>
     */
    public static function medianPathProvider(): array
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

        return [
            'field path' => [$items, 'value'],
            'callback' => [$items, static fn(array $item, int $key): int => $item['value']],
            'nested path' => [array_map(static fn(array $item): array => ['data' => $item], $items), 'data.value'],
        ];
    }

    public function testMedian(): void
    {
        $collection = new Collection([2, 4, 3, 4, 1, 5, 3]);

        $this->assertSame(
            3,
            $collection->median()
        );
    }

    public function testMedianEmpty(): void
    {
        $this->assertNull(
            Collection::empty()->median()
        );
    }

    public function testMedianEven(): void
    {
        $collection = new Collection([2, 4, 3, 4, 1, 5, 3, 9]);

        $this->assertSame(
            3.5,
            $collection->median()
        );
    }

    /**
     * @param array<array<string, mixed>> $values
     */
    #[DataProvider('medianPathProvider')]
    public function testMedianPath(array $values, Closure|string $valuePath): void
    {
        $collection = new Collection($values);

        $this->assertSame(
            3,
            $collection->median($valuePath)
        );
    }
}
