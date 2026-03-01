<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait CombineTestTrait
{
    public function testCombine(): void
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

        $this->assertSame(
            [1 => 'a', 2 => 'b'],
            $collection->combine('id', 'value')->toArray()
        );
    }

    public function testCombineCallback(): void
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

        $this->assertSame(
            [1 => 'a', 2 => 'b'],
            $collection->combine(
                static fn(array $item, int $key): int => $item['id'],
                static fn(array $item, int $key): string => $item['value'],
            )->toArray()
        );
    }

    public function testCombineDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 'a',
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 'b',
                ],
            ],
        ]);

        $this->assertSame(
            [1 => 'a', 2 => 'b'],
            $collection->combine('data.id', 'data.value')->toArray()
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

        $this->assertSame(
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
