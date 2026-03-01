<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait PrintNestedTestTrait
{
    public function testPrintNested(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Test 1',
                'children' => [
                    [
                        'id' => 2,
                        'parent_id' => 1,
                        'name' => 'Test 2',
                        'children' => [],
                    ],
                    [
                        'id' => 3,
                        'parent_id' => 1,
                        'name' => 'Test 3',
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 5,
                'parent_id' => null,
                'name' => 'Test 5',
                'children' => [
                    [
                        'id' => 4,
                        'parent_id' => 5,
                        'name' => 'Test 4',
                        'children' => [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                1 => 'Test 1',
                2 => '--Test 2',
                3 => '--Test 3',
                5 => 'Test 5',
                4 => '--Test 4',
            ],
            $collection->printNested('name')->toArray()
        );
    }

    public function testPrintNestedArguments(): void
    {
        $collection = new Collection([
            [
                'value' => 1,
                'parent_value' => null,
                'name' => 'Test 1',
                'items' => [
                    [
                        'value' => 2,
                        'parent_value' => 1,
                        'name' => 'Test 2',
                        'items' => [],
                    ],
                    [
                        'value' => 3,
                        'parent_value' => 1,
                        'name' => 'Test 3',
                        'items' => [],
                    ],
                ],
            ],
            [
                'value' => 5,
                'parent_value' => null,
                'name' => 'Test 5',
                'items' => [
                    [
                        'value' => 4,
                        'parent_value' => 5,
                        'name' => 'Test 4',
                        'items' => [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                1 => 'Test 1',
                2 => '- Test 2',
                3 => '- Test 3',
                5 => 'Test 5',
                4 => '- Test 4',
            ],
            $collection->printNested('name', 'value', '- ', 'items')->toArray()
        );
    }

    public function testPrintNestedDeep(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Test 1',
                'children' => [
                    [
                        'id' => 2,
                        'parent_id' => 1,
                        'name' => 'Test 2',
                        'children' => [
                            [
                                'id' => 3,
                                'parent_id' => 2,
                                'name' => 'Test 3',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                1 => 'Test 1',
                2 => '--Test 2',
                3 => '----Test 3',
            ],
            $collection->printNested('name')->toArray()
        );
    }
}
