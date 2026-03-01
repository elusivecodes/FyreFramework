<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait GroupByTestTrait
{
    public function testGroupBy(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 2,
            ],
            [
                'id' => 2,
                'value' => 4,
            ],
            [
                'id' => 3,
                'value' => 3,
            ],
            [
                'id' => 4,
                'value' => 4,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
            [
                'id' => 6,
                'value' => 5,
            ],
            [
                'id' => 7,
                'value' => 3,
            ],
        ]);

        $this->assertSame(
            [
                2 => [
                    [
                        'id' => 1,
                        'value' => 2,
                    ],
                ],
                4 => [
                    [
                        'id' => 2,
                        'value' => 4,
                    ],
                    [
                        'id' => 4,
                        'value' => 4,
                    ],
                ],
                3 => [
                    [
                        'id' => 3,
                        'value' => 3,
                    ],
                    [
                        'id' => 7,
                        'value' => 3,
                    ],
                ],
                1 => [
                    [
                        'id' => 5,
                        'value' => 1,
                    ],
                ],
                5 => [
                    [
                        'id' => 6,
                        'value' => 5,
                    ],
                ],
            ],
            $collection->groupBy('value')->toArray()
        );
    }

    public function testGroupByCallback(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 2,
            ],
            [
                'id' => 2,
                'value' => 4,
            ],
            [
                'id' => 3,
                'value' => 3,
            ],
            [
                'id' => 4,
                'value' => 4,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
            [
                'id' => 6,
                'value' => 5,
            ],
            [
                'id' => 7,
                'value' => 3,
            ],
        ]);

        $this->assertSame(
            [
                2 => [
                    [
                        'id' => 1,
                        'value' => 2,
                    ],
                ],
                4 => [
                    [
                        'id' => 2,
                        'value' => 4,
                    ],
                    [
                        'id' => 4,
                        'value' => 4,
                    ],
                ],
                3 => [
                    [
                        'id' => 3,
                        'value' => 3,
                    ],
                    [
                        'id' => 7,
                        'value' => 3,
                    ],
                ],
                1 => [
                    [
                        'id' => 5,
                        'value' => 1,
                    ],
                ],
                5 => [
                    [
                        'id' => 6,
                        'value' => 5,
                    ],
                ],
            ],
            $collection->groupBy(static fn(array $item, int $key): int => $item['value'])->toArray()
        );
    }

    public function testGroupByDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 2,
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'id' => 3,
                    'value' => 3,
                ],
            ],
            [
                'data' => [
                    'id' => 4,
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'id' => 5,
                    'value' => 1,
                ],
            ],
            [
                'data' => [
                    'id' => 6,
                    'value' => 5,
                ],
            ],
            [
                'data' => [
                    'id' => 7,
                    'value' => 3,
                ],
            ],
        ]);

        $this->assertSame(
            [
                2 => [
                    [
                        'data' => [
                            'id' => 1,
                            'value' => 2,
                        ],
                    ],
                ],
                4 => [
                    [
                        'data' => [
                            'id' => 2,
                            'value' => 4,
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 4,
                            'value' => 4,
                        ],
                    ],
                ],
                3 => [
                    [
                        'data' => [
                            'id' => 3,
                            'value' => 3,
                        ],
                    ],
                    [
                        'data' => [
                            'id' => 7,
                            'value' => 3,
                        ],
                    ],
                ],
                1 => [
                    [
                        'data' => [
                            'id' => 5,
                            'value' => 1,
                        ],
                    ],
                ],
                5 => [
                    [
                        'data' => [
                            'id' => 6,
                            'value' => 5,
                        ],
                    ],
                ],
            ],
            $collection->groupBy('data.value')->toArray()
        );
    }
}
