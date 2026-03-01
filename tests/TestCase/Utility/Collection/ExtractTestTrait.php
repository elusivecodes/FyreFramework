<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ExtractTestTrait
{
    public function testExtract(): void
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
            [2, 4, 3, 4, 1, 5, 3],
            $collection->extract('value')->toArray()
        );
    }

    public function testExtractCallback(): void
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
            [2, 4, 3, 4, 1, 5, 3],
            $collection->extract(static fn(array $item, int $key): int => $item['value'])->toArray()
        );
    }

    public function testExtractDeep(): void
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
            [2, 4, 3, 4, 1, 5, 3],
            $collection->extract('data.value')->toArray()
        );
    }
}
