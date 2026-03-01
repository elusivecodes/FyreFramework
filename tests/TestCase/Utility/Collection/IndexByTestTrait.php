<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait IndexByTestTrait
{
    public function testIndexBy(): void
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
                1 => [
                    'id' => 1,
                    'value' => 2,
                ],
                2 => [
                    'id' => 2,
                    'value' => 4,
                ],
                3 => [
                    'id' => 3,
                    'value' => 3,
                ],
                4 => [
                    'id' => 4,
                    'value' => 4,
                ],
                5 => [
                    'id' => 5,
                    'value' => 1,
                ],
                6 => [
                    'id' => 6,
                    'value' => 5,
                ],
                7 => [
                    'id' => 7,
                    'value' => 3,
                ],
            ],
            $collection->indexBy('id')->toArray()
        );
    }

    public function testIndexByCallback(): void
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
                1 => [
                    'id' => 1,
                    'value' => 2,
                ],
                2 => [
                    'id' => 2,
                    'value' => 4,
                ],
                3 => [
                    'id' => 3,
                    'value' => 3,
                ],
                4 => [
                    'id' => 4,
                    'value' => 4,
                ],
                5 => [
                    'id' => 5,
                    'value' => 1,
                ],
                6 => [
                    'id' => 6,
                    'value' => 5,
                ],
                7 => [
                    'id' => 7,
                    'value' => 3,
                ],
            ],
            $collection->indexBy(static fn(array $item, int $key): int => $item['id'])->toArray()
        );
    }

    public function testIndexByDeep(): void
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
                1 => [
                    'data' => [
                        'id' => 1,
                        'value' => 2,
                    ],
                ],
                2 => [
                    'data' => [
                        'id' => 2,
                        'value' => 4,
                    ],
                ],
                3 => [
                    'data' => [
                        'id' => 3,
                        'value' => 3,
                    ],
                ],
                4 => [
                    'data' => [
                        'id' => 4,
                        'value' => 4,
                    ],
                ],
                5 => [
                    'data' => [
                        'id' => 5,
                        'value' => 1,
                    ],
                ],
                6 => [
                    'data' => [
                        'id' => 6,
                        'value' => 5,
                    ],
                ],
                7 => [
                    'data' => [
                        'id' => 7,
                        'value' => 3,
                    ],
                ],
            ],
            $collection->indexBy('data.id')->toArray()
        );
    }
}
