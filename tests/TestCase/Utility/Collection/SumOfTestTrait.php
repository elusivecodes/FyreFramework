<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Closure;
use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

trait SumOfTestTrait
{
    /**
     * @return array<string, array{array<array<string, mixed>>, Closure|string}>
     */
    public static function sumOfPathProvider(): array
    {
        $items = [
            ['id' => 1, 'value' => 1],
            ['id' => 2, 'value' => 2],
            ['id' => 3, 'value' => 3],
            ['id' => 4, 'value' => 4],
            ['id' => 5, 'value' => 5],
            ['id' => 6, 'value' => 6],
            ['id' => 7, 'value' => 7],
            ['id' => 8, 'value' => 8],
            ['id' => 9, 'value' => 9],
            ['id' => 10, 'value' => 10],
        ];

        return [
            'field path' => [$items, 'value'],
            'callback' => [$items, static fn(array $item, int $key): int => $item['value']],
            'nested path' => [array_map(static fn(array $item): array => ['data' => $item], $items), 'data.value'],
        ];
    }

    public function testSumOf(): void
    {
        $this->assertSame(
            55,
            Collection::range(1, 10)->sumOf()
        );
    }

    public function testSumOfEmpty(): void
    {
        $this->assertSame(
            0,
            Collection::empty()->sumOf()
        );
    }

    /**
     * @param array<array<string, mixed>> $values
     */
    #[DataProvider('sumOfPathProvider')]
    public function testSumOfPath(array $values, Closure|string $valuePath): void
    {
        $collection = new Collection($values);

        $this->assertSame(
            55,
            $collection->sumOf($valuePath)
        );
    }
}
