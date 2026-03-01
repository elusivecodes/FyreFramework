<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait SortByTestTrait
{
    public function testSortBy(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 'Item 101',
            ],
            [
                'id' => 2,
                'value' => 'Item 10',
            ],
            [
                'id' => 3,
                'value' => 'Item 1',
            ],
            [
                'id' => 4,
                'value' => 'Item 11',
            ],
            [
                'id' => 5,
                'value' => 'Item 100',
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 3,
                    'value' => 'Item 1',
                ],
                [
                    'id' => 2,
                    'value' => 'Item 10',
                ],
                [
                    'id' => 4,
                    'value' => 'Item 11',
                ],
                [
                    'id' => 5,
                    'value' => 'Item 100',
                ],
                [
                    'id' => 1,
                    'value' => 'Item 101',
                ],
            ],
            $collection
                ->sortBy('value')
                ->values()
                ->toArray()
        );
    }

    public function testSortByCallback(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 'Item 101',
            ],
            [
                'id' => 2,
                'value' => 'Item 10',
            ],
            [
                'id' => 3,
                'value' => 'Item 1',
            ],
            [
                'id' => 4,
                'value' => 'Item 11',
            ],
            [
                'id' => 5,
                'value' => 'Item 100',
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 3,
                    'value' => 'Item 1',
                ],
                [
                    'id' => 2,
                    'value' => 'Item 10',
                ],
                [
                    'id' => 4,
                    'value' => 'Item 11',
                ],
                [
                    'id' => 5,
                    'value' => 'Item 100',
                ],
                [
                    'id' => 1,
                    'value' => 'Item 101',
                ],
            ],
            $collection
                ->sortBy(static fn(array $item): string => $item['value'])
                ->values()
                ->toArray()
        );
    }

    public function testSortByDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 'Item 101',
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 'Item 10',
                ],
            ],
            [
                'data' => [
                    'id' => 3,
                    'value' => 'Item 1',
                ],
            ],
            [
                'data' => [
                    'id' => 4,
                    'value' => 'Item 11',
                ],
            ],
            [
                'data' => [
                    'id' => 5,
                    'value' => 'Item 100',
                ],
            ],
        ]);

        $this->assertSame(
            [
                [
                    'data' => [
                        'id' => 3,
                        'value' => 'Item 1',
                    ],
                ],
                [
                    'data' => [
                        'id' => 2,
                        'value' => 'Item 10',
                    ],
                ],
                [
                    'data' => [
                        'id' => 4,
                        'value' => 'Item 11',
                    ],
                ],
                [
                    'data' => [
                        'id' => 5,
                        'value' => 'Item 100',
                    ],
                ],
                [
                    'data' => [
                        'id' => 1,
                        'value' => 'Item 101',
                    ],
                ],
            ],
            $collection
                ->sortBy('data.value')
                ->values()
                ->toArray()
        );
    }

    public function testSortByDescending(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 1.5,
            ],
            [
                'id' => 2,
                'value' => 1.25,
            ],
            [
                'id' => 3,
                'value' => 2,
            ],
            [
                'id' => 4,
                'value' => 1.75,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 3,
                    'value' => 2,
                ],
                [
                    'id' => 4,
                    'value' => 1.75,
                ],
                [
                    'id' => 1,
                    'value' => 1.5,
                ],
                [
                    'id' => 2,
                    'value' => 1.25,
                ],
                [
                    'id' => 5,
                    'value' => 1,
                ],
            ],
            $collection
                ->sortBy('value', Collection::SORT_NUMERIC, true)
                ->values()
                ->toArray()
        );
    }

    public function testSortByFlag(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 1.5,
            ],
            [
                'id' => 2,
                'value' => 1.25,
            ],
            [
                'id' => 3,
                'value' => 2,
            ],
            [
                'id' => 4,
                'value' => 1.75,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 5,
                    'value' => 1,
                ],
                [
                    'id' => 2,
                    'value' => 1.25,
                ],
                [
                    'id' => 1,
                    'value' => 1.5,
                ],
                [
                    'id' => 4,
                    'value' => 1.75,
                ],
                [
                    'id' => 3,
                    'value' => 2,
                ],
            ],
            $collection
                ->sortBy('value', Collection::SORT_NUMERIC)
                ->values()
                ->toArray()
        );
    }
}
