<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait MedianTestTrait
{
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

    public function testMedianPath(): void
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
            3,
            $collection->median('value')
        );
    }

    public function testMedianPathCallback(): void
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
            3,
            $collection->median(static fn(array $item, int $key): int => $item['value'])
        );
    }

    public function testMedianPathDeep(): void
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
            3,
            $collection->median('data.value')
        );
    }
}
