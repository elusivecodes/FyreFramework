<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait CountByTestTrait
{
    public function testCountBy(): void
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
                'value' => 4,
            ],
            [
                'id' => 6,
                'value' => 2,
            ],
        ]);

        $this->assertSame(
            [
                2 => 2,
                4 => 3,
                3 => 1,
            ],
            $collection->countBy('value')->toArray()
        );
    }

    public function testCountByCallback(): void
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
                'value' => 4,
            ],
            [
                'id' => 6,
                'value' => 2,
            ],
        ]);

        $this->assertSame(
            [
                2 => 2,
                4 => 3,
                3 => 1,
            ],
            $collection->countBy(static fn(array $item, int $key): int => $item['value'])->toArray()
        );
    }

    public function testCountByDeep(): void
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
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'id' => 6,
                    'value' => 2,
                ],
            ],
        ]);

        $this->assertSame(
            [
                2 => 2,
                4 => 3,
                3 => 1,
            ],
            $collection->countBy('data.value')->toArray()
        );
    }
}
