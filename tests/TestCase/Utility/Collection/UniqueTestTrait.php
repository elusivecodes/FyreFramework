<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait UniqueTestTrait
{
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

        $this->assertSame(
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

    public function testUniquePath(): void
    {
        $collection = new Collection([
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
            [
                'value' => 3,
            ],
            [
                'value' => 3,
            ],
            [
                'value' => 4,
            ],
            [
                'value' => 5,
            ],
        ]);

        $this->assertSame(
            [
                0 => [
                    'value' => 1,
                ],
                1 => [
                    'value' => 2,
                ],
                2 => [
                    'value' => 3,
                ],
                4 => [
                    'value' => 4,
                ],
                5 => [
                    'value' => 5,
                ],
            ],
            $collection
                ->unique('value')
                ->toArray()
        );
    }

    public function testUniquePathCallback(): void
    {
        $collection = new Collection([
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
            [
                'value' => 3,
            ],
            [
                'value' => 3,
            ],
            [
                'value' => 4,
            ],
            [
                'value' => 5,
            ],
        ]);

        $this->assertSame(
            [
                0 => [
                    'value' => 1,
                ],
                1 => [
                    'value' => 2,
                ],
                2 => [
                    'value' => 3,
                ],
                4 => [
                    'value' => 4,
                ],
                5 => [
                    'value' => 5,
                ],
            ],
            $collection
                ->unique(static fn(array $item, int $key): int => $item['value'])
                ->toArray()
        );
    }

    public function testUniquePathDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'value' => 1,
                ],
            ],
            [
                'data' => [
                    'value' => 2,
                ],
            ],
            [
                'data' => [
                    'value' => 3,
                ],
            ],
            [
                'data' => [
                    'value' => 3,
                ],
            ],
            [
                'data' => [
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'value' => 5,
                ],
            ],
        ]);

        $this->assertSame(
            [
                0 => [
                    'data' => [
                        'value' => 1,
                    ],
                ],
                1 => [
                    'data' => [
                        'value' => 2,
                    ],
                ],
                2 => [
                    'data' => [
                        'value' => 3,
                    ],
                ],
                4 => [
                    'data' => [
                        'value' => 4,
                    ],
                ],
                5 => [
                    'data' => [
                        'value' => 5,
                    ],
                ],
            ],
            $collection
                ->unique('data.value')
                ->toArray()
        );
    }
}
